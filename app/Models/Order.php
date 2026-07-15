<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
    'address', 'city', 'state', 'pincode', 'delivery_notes',
    'payment_method', 'payment_status', 'status',
    'subtotal', 'shipping', 'total', 'commission_credited', 'placed_at',
])]
class Order extends Model
{
    use HasFactory;

    /**
     * Forward status progression used by the admin order manager.
     */
    public const STATUS_FLOW = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'commission_credited' => 'boolean',
            'placed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(CommissionEarning::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }
}
