<?php

namespace App\Services;

use App\Models\Order;
use App\Models\VipMicrosite;

/**
 * Resolves which VIP storefront the customer is shopping so cart/checkout/order
 * pages keep that store's branding and send "Continue Shopping" back to the VIP
 * page instead of the main site. Derived from the cart (while shopping) or from
 * the order (on the confirmation page, after the cart is cleared).
 */
class StorefrontContext
{
    private bool $resolved = false;

    private ?VipMicrosite $current = null;

    public function __construct(private readonly CartService $cart)
    {
    }

    /**
     * The store implied by the current cart — set only when every seller-attributed
     * item belongs to the same VIP microsite.
     */
    public function current(): ?VipMicrosite
    {
        if ($this->resolved) {
            return $this->current;
        }

        $this->resolved = true;

        $sellerIds = $this->cart->items()->pluck('seller_id')->filter()->unique();

        if ($sellerIds->count() === 1) {
            $this->current = $this->resolveMicrosite($sellerIds->first());
        }

        return $this->current;
    }

    public function has(): bool
    {
        return $this->current() !== null;
    }

    /**
     * The store an order was placed from (single distinct seller across its items).
     */
    public function forOrder(Order $order): ?VipMicrosite
    {
        $order->loadMissing('items');

        $sellerIds = $order->items->pluck('seller_microsite_id')->filter()->unique();

        return $sellerIds->count() === 1 ? $this->resolveMicrosite($sellerIds->first()) : null;
    }

    /**
     * Where "Continue Shopping" should go: back to the VIP store when there is one,
     * otherwise the main product catalog.
     */
    public function continueUrl(?VipMicrosite $store = null): string
    {
        $store ??= $this->current();

        return $store
            ? url($store->publicPath()).'#shop'
            : route('products.index');
    }

    private function resolveMicrosite(int $id): ?VipMicrosite
    {
        return VipMicrosite::with('city')->where('id', $id)->where('status', 'active')->first();
    }
}
