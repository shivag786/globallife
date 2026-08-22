<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A priced checkout waiting on Razorpay. See the migration for why the cart is
 * snapshotted here rather than read from the session at confirmation time.
 */
#[Fillable([
    'razorpay_order_id', 'user_id',
    'customer_name', 'customer_phone', 'address', 'city', 'state', 'pincode',
    'delivery_notes', 'items', 'subtotal', 'shipping', 'total', 'currency',
    'status', 'failure_reason', 'order_id', 'razorpay_payment_id', 'completed_at',
])]
class PendingOrder extends Model
{
    protected function casts(): array
    {
        return [
            'items' => 'array',
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * The address snapshot in the shape OrderService expects.
     *
     * @return array<string, mixed>
     */
    public function deliveryData(): array
    {
        return [
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'delivery_notes' => $this->delivery_notes,
        ];
    }
}
