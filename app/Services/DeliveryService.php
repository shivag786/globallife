<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

/**
 * The store's delivery promise. A single global "delivery days" setting (admin
 * editable) drives the "Delivery by …" date shown on every product page and the
 * default estimated delivery date stamped onto each order at checkout.
 */
class DeliveryService
{
    public const DEFAULT_DAYS = 5;

    public function __construct(private readonly SettingsService $settings)
    {
    }

    /**
     * Global promised delivery window, in days.
     */
    public function days(): int
    {
        $days = (int) ($this->settings->get('delivery_days') ?? self::DEFAULT_DAYS);

        return max(0, $days);
    }

    /**
     * The delivery date to advertise on product pages: today + delivery days (IST).
     */
    public function promiseDate(): Carbon
    {
        return Carbon::now('Asia/Kolkata')->addDays($this->days())->startOfDay();
    }

    /**
     * The effective estimated delivery date for an order — the admin-set date when
     * present, otherwise placed_at + delivery days.
     */
    public function expectedFor(Order $order): ?Carbon
    {
        if ($order->expected_delivery_date) {
            return $order->expected_delivery_date;
        }

        return $order->placed_at?->copy()->addDays($this->days());
    }
}
