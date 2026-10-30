<?php

namespace App\Services;

use App\Models\BusinessProduct;
use App\Models\Product;
use App\Models\VipMicrosite;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart. Only product ids, seller (VIP microsite) ids, and
 * quantities are stored; every price is looked up fresh from the database at read
 * time, so client/session data can never influence what a customer is charged.
 *
 * Two kinds of line:
 *  - catalog  — a company Product, optionally sold from a VIP page (seller_id),
 *               which earns that page's upline commission;
 *  - business — a business's OWN product (BusinessProduct), which that business
 *               delivers itself. Lines from older sessions have no `type` and are
 *               catalog lines.
 *
 * Business lines cannot share a cart with anything else: one business delivers
 * the whole order, so the cart holds either company products or one business's
 * products (see conflictFor()).
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public const FREE_SHIPPING_THRESHOLD = 999.0;

    public const FLAT_SHIPPING = 0.0;

    public const MAX_QTY = 99;

    public const TYPE_CATALOG = 'catalog';

    public const TYPE_BUSINESS = 'business';

    /**
     * @return array<string, array{product_id: int, seller_id: ?int, quantity: int, type?: string}>
     */
    private function lines(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /**
     * @param  array<string, array{product_id: int, seller_id: ?int, quantity: int, type?: string}>  $lines
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

    /**
     * Line key for a business's own product. Prefixed so it can never collide
     * with a catalog product that happens to share the numeric id.
     */
    public function businessKeyFor(int $businessProductId, int $micrositeId): string
    {
        return 'b'.$businessProductId.':'.$micrositeId;
    }

    public function businessQuantityFor(int $businessProductId, int $micrositeId): int
    {
        return $this->lines()[$this->businessKeyFor($businessProductId, $micrositeId)]['quantity'] ?? 0;
    }

    /**
     * Why the given line cannot join the current cart, or NULL when it can.
     *
     * A business delivers its own products, so its lines may only sit with more
     * of the same business's lines. Company products may sit together from any
     * number of VIP pages, as before.
     */
    public function conflictFor(string $type, ?int $sellerId): ?string
    {
        foreach ($this->lines() as $line) {
            $lineType = $line['type'] ?? self::TYPE_CATALOG;

            if ($type === self::TYPE_CATALOG && $lineType === self::TYPE_BUSINESS) {
                return 'Your cart has products from a business that delivers its own orders. Place that order first, or clear your cart to add this item.';
            }

            if ($type === self::TYPE_BUSINESS
                && ($lineType !== self::TYPE_BUSINESS || (int) $line['seller_id'] !== (int) $sellerId)) {
                return 'Your cart already has items from another shop. Each business delivers its own orders, so please place that order first, or clear your cart to add this item.';
            }
        }

        return null;
    }

    public function add(int $productId, ?int $sellerId = null, int $quantity = 1): void
    {
        $lines = $this->lines();
        $key = $this->key($productId, $sellerId);
        $existing = $lines[$key]['quantity'] ?? 0;

        $lines[$key] = [
            'type' => self::TYPE_CATALOG,
            'product_id' => $productId,
            'seller_id' => $sellerId ?: null,
            'quantity' => min(self::MAX_QTY, max(1, $existing + $quantity)),
        ];

        $this->save($lines);
    }

    public function addBusiness(int $businessProductId, int $micrositeId, int $quantity = 1): void
    {
        $lines = $this->lines();
        $key = $this->businessKeyFor($businessProductId, $micrositeId);
        $existing = $lines[$key]['quantity'] ?? 0;

        $lines[$key] = [
            'type' => self::TYPE_BUSINESS,
            'product_id' => $businessProductId,
            'seller_id' => $micrositeId,
            'quantity' => min(self::MAX_QTY, max(1, $existing + $quantity)),
        ];

        $this->save($lines);
    }

    /**
     * The business that will deliver this cart, when it holds business lines.
     */
    public function fulfillingMicrositeId(): ?int
    {
        foreach ($this->lines() as $line) {
            if (($line['type'] ?? self::TYPE_CATALOG) === self::TYPE_BUSINESS) {
                return (int) $line['seller_id'];
            }
        }

        return null;
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
     * Every row carries the same display keys (name, sku, image, url) whatever
     * its type, so the cart and checkout views never need to know which kind of
     * product they are showing. `product` is the live Product or BusinessProduct.
     *
     * @return Collection<int, array{key: string, type: string, product: Product|BusinessProduct, product_id: ?int, business_product_id: ?int, name: string, sku: ?string, image: ?string, url: string, seller: ?VipMicrosite, seller_id: ?int, quantity: int, unit_price: float, line_total: float}>
     */
    public function items(): Collection
    {
        $lines = $this->lines();
        if (empty($lines)) {
            return collect();
        }

        $byType = collect($lines)->groupBy(fn ($line) => $line['type'] ?? self::TYPE_CATALOG);

        $catalogIds = $byType->get(self::TYPE_CATALOG, collect())->pluck('product_id')->unique();
        $products = $catalogIds->isNotEmpty()
            ? Product::whereIn('id', $catalogIds)->where('status', 'active')->get()->keyBy('id')
            : collect();

        $businessIds = $byType->get(self::TYPE_BUSINESS, collect())->pluck('product_id')->unique();
        $businessProducts = $businessIds->isNotEmpty()
            ? BusinessProduct::with(['vipMicrosite.vipPlan', 'vipMicrosite.city', 'vipMicrosite.user'])
                ->whereIn('id', $businessIds)->get()->keyBy('id')
            : collect();

        $sellerIds = collect($lines)->pluck('seller_id')->filter()->unique();
        $sellers = $sellerIds->isNotEmpty()
            ? VipMicrosite::with('city')->whereIn('id', $sellerIds)->get()->keyBy('id')
            : collect();

        $items = collect();
        foreach ($lines as $key => $line) {
            $row = ($line['type'] ?? self::TYPE_CATALOG) === self::TYPE_BUSINESS
                ? $this->businessRow($businessProducts->get($line['product_id']), $line)
                : $this->catalogRow($products->get($line['product_id']), $line);

            if ($row === null) {
                continue;
            }

            $unit = (float) $row['product']->sellingPrice();
            $items->push(['key' => $key] + $row + [
                'seller' => $line['seller_id'] ? $sellers->get($line['seller_id']) : null,
                'seller_id' => $line['seller_id'],
                'unit_price' => $unit,
                'line_total' => round($unit * $row['quantity'], 2),
            ]);
        }

        return $items;
    }

    /**
     * @param  array{product_id: int, seller_id: ?int, quantity: int}  $line
     * @return array<string, mixed>|null
     */
    private function catalogRow(?Product $product, array $line): ?array
    {
        if (! $product || ! $product->hasPrice()) {
            return null;
        }

        return [
            'type' => self::TYPE_CATALOG,
            'product' => $product,
            'product_id' => $product->id,
            'business_product_id' => null,
            'name' => $product->name,
            'sku' => $product->sku,
            'image' => $product->main_image,
            'url' => route('products.show', $product),
            'quantity' => (int) $line['quantity'],
        ];
    }

    /**
     * A business line survives only while the product is still for sale on a
     * live direct-sell page, and still belongs to the business it was added
     * from. Quantity is clamped to the stock the owner has left.
     *
     * @param  array{product_id: int, seller_id: ?int, quantity: int}  $line
     * @return array<string, mixed>|null
     */
    private function businessRow(?BusinessProduct $product, array $line): ?array
    {
        if (! $product
            || (int) $product->vip_microsite_id !== (int) $line['seller_id']
            || ! $product->isPurchasable()) {
            return null;
        }

        $quantity = (int) $line['quantity'];
        if ($product->stock !== null) {
            $quantity = min($quantity, (int) $product->stock);
        }

        return [
            'type' => self::TYPE_BUSINESS,
            'product' => $product,
            'product_id' => null,
            'business_product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'image' => $product->image_path,
            'url' => url($product->vipMicrosite->publicPath()).'#product-'.$product->id,
            'quantity' => $quantity,
        ];
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
