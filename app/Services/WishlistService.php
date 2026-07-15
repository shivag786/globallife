<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Simple session-backed wishlist holding product ids.
 */
class WishlistService
{
    private const SESSION_KEY = 'wishlist';

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
        return count($this->ids());
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

        return Product::whereIn('id', $ids)->where('status', 'active')->get();
    }
}
