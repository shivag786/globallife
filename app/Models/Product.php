<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'slug', 'category', 'category_id', 'brand_id', 'sku', 'tags', 'badge',
    'price', 'mrp', 'offer_price', 'stock', 'rating', 'review_count',
    'short_description', 'long_description', 'ingredients', 'usage_instructions',
    'main_image', 'gallery', 'specs', 'is_featured', 'status', 'display_order',
    'meta_title', 'meta_description', 'canonical_url',
])]
class Product extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'specs' => 'array',
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'rating' => 'decimal:2',
            'stock' => 'integer',
            'review_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(ProductBenefit::class)->orderBy('display_order');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The price the customer actually pays per unit — the offer price when set,
     * otherwise the regular selling price. This is the base for commission math.
     */
    public function sellingPrice(): ?float
    {
        $price = $this->offer_price ?? $this->price;

        return $price !== null ? (float) $price : null;
    }

    /**
     * Whether a customer-facing price has been set.
     */
    public function hasPrice(): bool
    {
        return $this->sellingPrice() !== null && $this->sellingPrice() > 0;
    }

    /**
     * Whether the MRP is higher than the price paid (i.e. there's a saving to show).
     */
    public function hasDiscount(): bool
    {
        return $this->hasPrice()
            && $this->mrp !== null
            && (float) $this->mrp > $this->sellingPrice();
    }

    /**
     * Rounded percentage saved off the MRP, or null when there's no discount.
     */
    public function discountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round(((float) $this->mrp - $this->sellingPrice()) / (float) $this->mrp * 100);
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }
}
