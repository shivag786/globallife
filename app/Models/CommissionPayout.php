<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'period', 'product_amount', 'vip_amount', 'amount',
    'wallet_debited', 'reference', 'note', 'paid_by', 'paid_at',
])]
class CommissionPayout extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'product_amount' => 'decimal:2',
            'vip_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'wallet_debited' => 'decimal:2',
            'paid_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
