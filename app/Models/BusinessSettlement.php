<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * The company transferring a business the money it collected online for that
 * business's own products. The orders it covered carry its id.
 */
#[Fillable([
    'vip_microsite_id', 'amount', 'orders_count', 'utr_number', 'payment_screenshot_path',
    'note', 'paid_by', 'paid_at',
])]
class BusinessSettlement extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'orders_count' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<VipMicrosite, $this>
     */
    public function vipMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
