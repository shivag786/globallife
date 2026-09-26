<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vip_microsite_id', 'image_path', 'name', 'slug', 'short_description', 'long_description',
    'category', 'tags', 'mrp', 'offer_price', 'discount_percent', 'strike_price', 'show_pricing',
    'meta_title', 'meta_description', 'meta_keywords', 'status', 'is_featured', 'sort_order', 'show_book_now',
])]
class BusinessService extends Model
{
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'show_pricing' => 'boolean',
            'is_featured' => 'boolean',
            'show_book_now' => 'boolean',
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
     * Pricing.
     *
     * Two figures are entered — MRP and sale price — and everything shown to a
     * customer is derived from them, so a struck-through price and a discount
     * badge can never disagree with what is actually charged. Mirrors the same
     * accessors on Product. The three cases fall out of the maths:
     *
     *   MRP only        → sellingPrice() is the MRP, so nothing to strike or save
     *   sale only       → no MRP to strike
     *   both, MRP higher → sale price, MRP struck through, percentage saved
     */

    /**
     * What the customer actually pays — the sale price when set, else the MRP.
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

    /**
     * Whether the MRP genuinely sits above the price paid, i.e. there is a real
     * saving to advertise. A sale price at or above the MRP is not a discount.
     */
    public function hasDiscount(): bool
    {
        return $this->hasPrice()
            && $this->mrp !== null
            && (float) $this->mrp > $this->sellingPrice();
    }

    /**
     * Rounded percentage off the MRP, or null when there is no discount.
     */
    public function discountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round(((float) $this->mrp - $this->sellingPrice()) / (float) $this->mrp * 100);
    }

    /**
     * The price to show struck through, or null when there is nothing to strike.
     */
    public function strikeThroughPrice(): ?float
    {
        return $this->hasDiscount() ? (float) $this->mrp : null;
    }
}
