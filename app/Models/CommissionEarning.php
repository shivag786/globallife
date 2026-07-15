<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'order_item_id', 'product_id', 'seller_microsite_id', 'beneficiary_id',
    'role', 'base_amount', 'type', 'value', 'amount', 'status', 'approved_at',
])]
class CommissionEarning extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'value' => 'decimal:2',
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function sellerMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class, 'seller_microsite_id');
    }
}
