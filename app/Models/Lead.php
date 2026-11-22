<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'email', 'phone', 'city', 'message', 'source',
    'interested_plan_id', 'status', 'assigned_manager_id', 'vip_microsite_id', 'product_id',
])]
class Lead extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return ['seen_at' => 'datetime'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logExcept(['seen_at'])->dontSubmitEmptyLogs();
    }

    /**
     * Leads no admin has opened yet.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Lead>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Lead>
     */
    public function scopeUnseen(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('seen_at');
    }

    public function isNew(): bool
    {
        return $this->seen_at === null;
    }

    /**
     * @return BelongsTo<VipPlan, $this>
     */
    public function interestedPlan(): BelongsTo
    {
        return $this->belongsTo(VipPlan::class, 'interested_plan_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    /**
     * @return BelongsTo<VipMicrosite, $this>
     */
    public function vipMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class);
    }

    /**
     * The catalogue product a product-page enquiry was sent from. This, not the
     * message text, is the record of which product the enquiry is about.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
