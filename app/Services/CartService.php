<?php

namespace App\Services;

use App\Models\Product;
use App\Models\VipMicrosite;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart. Only product ids, seller (VIP microsite) ids, and
 * quantities are stored; every price is looked up fresh from the database at read
 * time, so client/session data can never influence what a customer is charged.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public const FREE_SHIPPING_THRESHOLD = 999.0;

    public const FLAT_SHIPPING = 49.0;

    public const MAX_QTY = 99;

    /**
     * @return array<string, array{product_id: int, seller_id: ?int, quantity: int}>
     */
    private function lines(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /**
     * @param  array<string, array{product_id: int, seller_id: ?int, quantity: int}>  $lines
     */
    private function save(array $lines): void
    {
        session([self::SESSION_KEY => $lines]);
    }

    private function key(int $productId, ?int $sellerId): string
    {
        return $productId.':'.($sellerId ?: 0);
    }

    /**
     * Public line key for a product+seller — used by storefront cards to drive the
     * quantity stepper once an item is in the cart.
     */
    public function keyFor(int $productId, ?int $sellerId): string
    {
        return $this->key($productId, $sellerId);
    }

    public function quantityFor(int $productId, ?int $sellerId): int
    {
        return $this->lines()[$this->key($productId, $sellerId)]['quantity'] ?? 0;
    }

    public function add(int $productId, ?int $sellerId = null, int $quantity = 1): void
    {
        $lines = $this->lines();
        $key = $this->key($productId, $sellerId);
        $existing = $lines[$key]['quantity'] ?? 0;

        $lines[$key] = [
            'product_id' => $productId,
            'seller_id' => $sellerId ?: null,
            'quantity' => min(self::MAX_QTY, max(1, $existing + $quantity)),
        ];

        $this->save($lines);
    }

    public function setQuantity(string $key, int $quantity): void
    {
        $lines = $this->lines();
        if (! isset($lines[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = min($quantity, self::MAX_QTY);
        }

        $this->save($lines);
    }

    public function remove(string $key): void
    {
        $lines = $this->lines();
        unset($lines[$key]);
        $this->save($lines);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(array_map(fn ($line) => $line['quantity'], $this->lines()));
    }

    public function isEmpty(): bool
    {
        return empty($this->lines());
    }

    /**
     * Hydrate cart lines into display rows, dropping anything no longer purchasable.
     *
     * @return Collection<int, array{key: string, product: Product, seller: ?VipMicrosite, seller_id: ?int, quantity: int, unit_price: float, line_total: float}>
     */
    public function items(): Collection
    {
        $lines = $this->lines();
        if (empty($lines)) {
            return collect();
        }

        $products = Product::whereIn('id', collect($lines)->pluck('product_id')->unique())
            ->where('status', 'active')->get()->keyBy('id');

        $sellerIds = collect($lines)->pluck('seller_id')->filter()->unique();
        $sellers = $sellerIds->isNotEmpty()
            ? VipMicrosite::with('city')->whereIn('id', $sellerIds)->get()->keyBy('id')
            : collect();

        $items = collect();
        foreach ($lines as $key => $line) {
            $product = $products->get($line['product_id']);
            if (! $product || ! $product->hasPrice()) {
                continue;
            }

            $unit = (float) $product->sellingPrice();
            $items->push([
                'key' => $key,
                'product' => $product,
                'seller' => $line['seller_id'] ? $sellers->get($line['seller_id']) : null,
                'seller_id' => $line['seller_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $unit,
                'line_total' => round($unit * $line['quantity'], 2),
            ]);
        }

        return $items;
    }

    /**
     * @return array{subtotal: float, shipping: float, total: float, count: int}
     */
    public function totals(): array
    {
        $items = $this->items();
        $subtotal = round($items->sum('line_total'), 2);
        $shipping = ($subtotal <= 0 || $subtotal >= self::FREE_SHIPPING_THRESHOLD) ? 0.0 : self::FLAT_SHIPPING;

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => round($subtotal + $shipping, 2),
            'count' => (int) $items->sum('quantity'),
        ];
    }
}
