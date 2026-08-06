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
    'expected_delivery_date', 'processing_at', 'dispatched_at', 'delivered_at',
])]
class Order extends Model
{
    use HasFactory;

    /**
     * Forward status progression used by the admin order manager.
     */
    public const STATUS_FLOW = ['pending', 'confirmed', 'processing', 'dispatched', 'delivered'];

    /**
     * Rank of each status along the fulfilment path — used to decide which
     * tracking steps are complete.
     */
    public const STATUS_RANK = [
        'pending' => 0, 'confirmed' => 1, 'processing' => 2, 'dispatched' => 3, 'delivered' => 4,
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'commission_credited' => 'boolean',
            'placed_at' => 'datetime',
            'expected_delivery_date' => 'date',
            'processing_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
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

    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'refunded'], true);
    }

    /**
     * Whether the fulfilment path has reached (or passed) a given status.
     */
    public function hasReached(string $status): bool
    {
        return (self::STATUS_RANK[$this->status] ?? 0) >= (self::STATUS_RANK[$status] ?? 99);
    }
}
