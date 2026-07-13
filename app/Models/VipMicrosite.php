<?php

namespace App\Models;

use App\Support\BusinessModules;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'city_id', 'vip_plan_id', 'business_name', 'business_slug',
    'description', 'secure_token', 'status', 'activated_at',
    'business_category', 'business_sub_category', 'owner_name', 'short_description',
    'establishment_year', 'gst_no', 'pan_no', 'cin_no', 'logo_path', 'cover_banner_path',
    'business_email', 'phone_number', 'alternate_number', 'whatsapp_number', 'website_url',
    'address', 'google_map_url', 'business_hours', 'holidays',
    'facebook_url', 'instagram_url', 'youtube_url', 'linkedin_url', 'twitter_url', 'telegram_url', 'pinterest_url',
    'module_visibility',
])]
class VipMicrosite extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'business_hours' => 'array',
            'holidays' => 'array',
            'module_visibility' => 'array',
            'activated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsTo<VipPlan, $this>
     */
    public function vipPlan(): BelongsTo
    {
        return $this->belongsTo(VipPlan::class);
    }

    /**
     * @return HasMany<BusinessBanner, $this>
     */
    public function banners(): HasMany
    {
        return $this->hasMany(BusinessBanner::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BusinessService, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(BusinessService::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BusinessProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(BusinessProduct::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BusinessGalleryItem, $this>
     */
    public function galleryItems(): HasMany
    {
        return $this->hasMany(BusinessGalleryItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BusinessVideo, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(BusinessVideo::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BusinessFaq, $this>
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(BusinessFaq::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BusinessReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class);
    }

    /**
     * @return HasMany<BusinessProfileEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(BusinessProfileEvent::class);
    }

    /**
     * @return HasOne<CommissionTransaction, $this>
     */
    public function commissionTransaction(): HasOne
    {
        return $this->hasOne(CommissionTransaction::class);
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }

    /**
     * Whether the given module/floating-button key is turned on. Unknown/missing
     * keys default to visible so existing profiles aren't silently hidden when a
     * new module is introduced.
     */
    public function isModuleVisible(string $key): bool
    {
        return (bool) (($this->module_visibility ?? [])[$key] ?? true);
    }

    public function setModuleVisibility(string $key, bool $visible): void
    {
        $map = $this->module_visibility ?? BusinessModules::defaults();
        $map[$key] = $visible;
        $this->update(['module_visibility' => $map]);
    }

    /**
     * The public, shareable path for this microsite, e.g. "/jhansi/lifeline-hospital/22-LSTWEFF-44".
     */
    public function publicPath(): string
    {
        return sprintf(
            '/%s/%s/%d-%s-%d',
            $this->city->slug,
            $this->business_slug,
            $this->user_id,
            $this->secure_token,
            $this->user->created_by,
        );
    }

    /**
     * A simple weighted completion score to power the Dashboard's "Profile Completion %" tile.
     */
    public function completionPercentage(): int
    {
        $checks = [
            (bool) $this->business_name,
            (bool) $this->description,
            (bool) $this->logo_path,
            (bool) $this->phone_number,
            (bool) $this->business_email,
            (bool) $this->address,
            $this->services()->exists() || $this->products()->exists(),
            $this->galleryItems()->exists(),
            $this->faqs()->exists(),
        ];

        $filled = count(array_filter($checks));

        return (int) round(($filled / count($checks)) * 100);
    }
}
