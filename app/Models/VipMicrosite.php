<?php

namespace App\Models;

use App\Support\BusinessModules;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'city_id', 'vip_plan_id', 'business_name', 'business_slug',
    'description', 'secure_token', 'status', 'activated_at', 'plan_expires_at',
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
            'plan_expires_at' => 'datetime',
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
     * Per-product storefront settings this VIP has saved. Distinct from
     * `products()`, which are the VIP's own free-text business products.
     *
     * A row here is an *override*, not an opt-in: see visibleCatalogProducts().
     *
     * @return BelongsToMany<Product, $this>
     */
    public function catalogProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'vip_products')
            ->withPivot(['is_visible', 'is_featured', 'display_order'])
            ->withTimestamps();
    }

    /**
     * Catalog products for the public storefront, featured first.
     *
     * The company catalog is on sale by default: every active product shows on
     * every VIP page unless that member has explicitly switched it off. So the
     * absence of a `vip_products` row means visible, and a product added to the
     * catalog tomorrow starts earning on every page without anyone opting in.
     * Only a row with `is_visible = 0` hides one.
     *
     * @return Collection<int, Product>
     */
    public function visibleCatalogProducts(): Collection
    {
        return Product::query()
            ->select('products.*')
            ->leftJoin('vip_products', function (JoinClause $join) {
                $join->on('vip_products.product_id', '=', 'products.id')
                    ->where('vip_products.vip_microsite_id', '=', $this->id);
            })
            ->where('products.status', 'active')
            // No row at all counts as visible.
            ->where(fn (Builder $q) => $q
                ->whereNull('vip_products.is_visible')
                ->orWhere('vip_products.is_visible', true))
            ->orderByDesc(DB::raw('COALESCE(vip_products.is_featured, 0)'))
            ->orderBy(DB::raw('COALESCE(vip_products.display_order, 0)'))
            ->orderBy('products.name')
            ->get();
    }

    /**
     * Whether this member currently shows the given catalog product. Mirrors the
     * default-on rule above, for one product.
     */
    public function showsCatalogProduct(Product $product): bool
    {
        $row = DB::table('vip_products')
            ->where('vip_microsite_id', $this->id)
            ->where('product_id', $product->id)
            ->first();

        return $row === null || (bool) $row->is_visible;
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

    /**
     * @return HasMany<VipRenewal, $this>
     */
    public function renewals(): HasMany
    {
        return $this->hasMany(VipRenewal::class);
    }

    public function isActivated(): bool
    {
        return $this->activated_at !== null;
    }

    /**
     * How long before expiry a renewal may be taken. Wide enough that a partner
     * collecting payment in person has a comfortable run-up, short enough that
     * the offer is not permanently on screen — a month covers a full billing
     * conversation even on the 3-month Growth package.
     */
    public const RENEWAL_WINDOW_DAYS = 30;

    /**
     * A plan is expired once its paid cycle has run out. A microsite that was
     * never activated has no expiry date and is therefore NOT expired — it is
     * simply awaiting its first activation, and keeps behaving as it always has.
     */
    public function hasExpiredPlan(): bool
    {
        return $this->plan_expires_at !== null && $this->plan_expires_at->isPast();
    }

    /**
     * Whether the public microsite may be served at all.
     *
     * Two ways to be off the air, and the visitor is told neither: the plan was
     * never activated (no payment recorded yet), or its paid cycle has run out.
     * Activation is the moment a microsite first goes live — before that it is
     * only a draft its owner is filling in.
     */
    public function isLive(): bool
    {
        return $this->isActivated() && ! $this->hasExpiredPlan();
    }

    /**
     * Whether a renewal can be taken now: inside the last month of the cycle, or
     * any time after it lapsed. Deliberately wider than `hasExpiredPlan()` — the
     * public page only goes down at true expiry, but the partner can collect and
     * record payment before that, so nobody's page ever has to go dark.
     */
    public function isRenewalDue(): bool
    {
        return $this->plan_expires_at !== null
            && now()->greaterThanOrEqualTo($this->plan_expires_at->copy()->subDays(self::RENEWAL_WINDOW_DAYS));
    }

    /**
     * Due for renewal but still live — the amber "expiring soon" state.
     */
    public function isExpiringSoon(): bool
    {
        return $this->isRenewalDue() && ! $this->hasExpiredPlan();
    }

    /**
     * Days until the plan runs out; negative once it already has.
     */
    public function daysUntilExpiry(): ?int
    {
        if ($this->plan_expires_at === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->plan_expires_at->startOfDay(), false);
    }

    /**
     * Content quota for one of the plan-capped content types.
     *
     * The cap applies to the TOTAL number of rows, so items created under a
     * bigger package still count after a downgrade. Nothing is ever hidden or
     * deleted when a member ends up over the cap — they simply cannot add more
     * until they renew onto a larger package.
     *
     * @param  'products'|'services'  $type
     * @return array{used: int, limit: int, remaining: int, can_add: bool, over: bool}
     */
    public function contentQuota(string $type): array
    {
        $plan = $this->vipPlan;

        $used = $type === 'products' ? $this->products()->count() : $this->services()->count();
        $limit = $type === 'products' ? (int) ($plan?->productLimit() ?? 0) : (int) ($plan?->serviceLimit() ?? 0);

        return [
            'used' => $used,
            'limit' => $limit,
            'remaining' => max(0, $limit - $used),
            'can_add' => $used < $limit,
            'over' => $used > $limit,
        ];
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
