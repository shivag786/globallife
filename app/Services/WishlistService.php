<?php

namespace App\Services;

use App\Models\BusinessProduct;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Simple session-backed wishlist holding product ids.
 *
 * Two lists: company catalogue products (`products`) and a VIP business's own
 * products shown on its microsite (`business_products`). They are separate
 * tables, so their ids are kept apart.
 */
class WishlistService
{
    private const SESSION_KEY = 'wishlist';

    private const BUSINESS_SESSION_KEY = 'wishlist_business';

    /**
     * @return array<int, int>
     */
    private function ids(): array
    {
        return array_map('intval', session(self::SESSION_KEY, []));
    }

    public function add(int $productId): void
    {
        $ids = $this->ids();
        if (! in_array($productId, $ids, true)) {
            $ids[] = $productId;
            session([self::SESSION_KEY => $ids]);
        }
    }

    public function remove(int $productId): void
    {
        session([self::SESSION_KEY => array_values(array_diff($this->ids(), [$productId]))]);
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function count(): int
    {
        return count($this->ids()) + count($this->businessIds());
    }

    /**
     * @return array<int, int>
     */
    private function businessIds(): array
    {
        return array_map('intval', session(self::BUSINESS_SESSION_KEY, []));
    }

    public function addBusiness(int $businessProductId): void
    {
        $ids = $this->businessIds();
        if (! in_array($businessProductId, $ids, true)) {
            $ids[] = $businessProductId;
            session([self::BUSINESS_SESSION_KEY => $ids]);
        }
    }

    public function removeBusiness(int $businessProductId): void
    {
        session([self::BUSINESS_SESSION_KEY => array_values(array_diff($this->businessIds(), [$businessProductId]))]);
    }

    public function hasBusiness(int $businessProductId): bool
    {
        return in_array($businessProductId, $this->businessIds(), true);
    }

    /**
     * Saved business products that are still published on a live microsite.
     *
     * @return Collection<int, BusinessProduct>
     */
    public function businessItems(): Collection
    {
        $ids = $this->businessIds();
        if (empty($ids)) {
            return collect();
        }

        return BusinessProduct::with(['vipMicrosite.city', 'vipMicrosite.user', 'vipMicrosite.vipPlan'])
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->whereHas('vipMicrosite', fn ($q) => $q->where('status', 'active'))
            ->get()
            ->filter(fn (BusinessProduct $product) => $product->vipMicrosite->isLive())
            ->values();
    }

    /**
     * @return Collection<int, Product>
     */
    public function items(): Collection
    {
        $ids = $this->ids();
        if (empty($ids)) {
            return collect();
        }

        return Product::onSale()->whereIn('id', $ids)->get();
    }
}
