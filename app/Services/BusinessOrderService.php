<?php

namespace App\Services;

use App\Models\Order;
use App\Models\VipMicrosite;
use RuntimeException;

/**
 * A business moving its own orders along as it packs and delivers them.
 *
 * Only forward steps, and only up to Delivered: cancelling or refunding an
 * order that may have been paid online is the company's call (its Razorpay
 * holds the money), so it stays on the admin order screen.
 */
class BusinessOrderService
{
    public const BUSINESS_STATUSES = [
        'processing' => 'Packed / Processing',
        'dispatched' => 'Out for delivery',
        'delivered' => 'Delivered',
    ];

    public function __construct(private readonly OrderService $orders)
    {
    }

    public function owns(VipMicrosite $microsite, Order $order): bool
    {
        return (int) $order->fulfilled_by_microsite_id === (int) $microsite->id;
    }

    /**
     * The statuses this order can still be moved to, in order.
     *
     * @return array<string, string>
     */
    public function nextStatuses(Order $order): array
    {
        if ($order->isCancelled() || $order->isDelivered()) {
            return [];
        }

        $rank = Order::STATUS_RANK[$order->status] ?? 0;

        return array_filter(
            self::BUSINESS_STATUSES,
            fn (string $status) => Order::STATUS_RANK[$status] > $rank,
            ARRAY_FILTER_USE_KEY,
        );
    }

    public function updateStatus(VipMicrosite $microsite, Order $order, string $status): void
    {
        if (! $this->owns($microsite, $order)) {
            throw new RuntimeException('This order is not yours to update.');
        }

        if (! array_key_exists($status, $this->nextStatuses($order))) {
            throw new RuntimeException('This order cannot be moved to that status.');
        }

        // Same path as the admin screen: Delivered stamps the date, marks a COD
        // order paid, and is idempotent.
        $this->orders->updateStatus($order, $status);
    }
}
