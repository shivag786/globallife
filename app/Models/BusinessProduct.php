<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vip_microsite_id', 'image_path', 'name', 'slug', 'short_description', 'long_description',
    'category', 'tags', 'mrp', 'offer_price', 'discount_percent', 'strike_price', 'show_pricing',
    'sku', 'stock', 'brand', 'weight', 'color', 'variant',
    'meta_title', 'meta_description', 'meta_keywords', 'status', 'is_featured', 'sort_order',
])]
class BusinessProduct extends Model
{
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'show_pricing' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<VipMicrosite, $this>
     */
    public function vipMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class);
    }

    /*
     * Pricing — derived from MRP and sale price exactly as on BusinessService,
     * so the badge, the struck-through figure and the amount charged at checkout
     * can never disagree.
     */

    public function sellingPrice(): ?float
    {
        $price = $this->offer_price ?? $this->mrp;

        return $price !== null ? (float) $price : null;
    }

    public function hasPrice(): bool
    {
        return $this->sellingPrice() !== null && $this->sellingPrice() > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->hasPrice()
            && $this->mrp !== null
            && (float) $this->mrp > $this->sellingPrice();
    }

    public function discountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round(((float) $this->mrp - $this->sellingPrice()) / (float) $this->mrp * 100);
    }

    public function strikeThroughPrice(): ?float
    {
        return $this->hasDiscount() ? (float) $this->mrp : null;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * A NULL stock means the owner does not track stock, which is not the same
     * as having none.
     */
    public function isInStock(): bool
    {
        return $this->stock === null || (int) $this->stock > 0;
    }

    /**
     * Whether a customer can put this in a cart right now. Every condition is
     * re-checked from the database whenever the cart is read, so a product
     * unpublished, repriced or sold out after it was added drops out of the
     * cart rather than being sold anyway.
     */
    public function isPurchasable(): bool
    {
        $microsite = $this->vipMicrosite;

        return $this->isPublished()
            && $this->show_pricing
            && $this->hasPrice()
            && $this->isInStock()
            && $microsite !== null
            && $microsite->status === 'active'
            && $microsite->isLive()
            && $microsite->sellsDirect();
    }
}
