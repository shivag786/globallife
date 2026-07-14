<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'slug', 'category', 'tags', 'badge', 'price', 'mrp', 'short_description', 'long_description',
    'main_image', 'specs', 'is_featured', 'status', 'display_order',
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
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
            'mrp' => 'decimal:2',
        ];
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
     * Whether a customer-facing price has been set.
     */
    public function hasPrice(): bool
    {
        return $this->price !== null && (float) $this->price > 0;
    }

    /**
     * Whether the MRP is higher than the selling price (i.e. there's a saving to show).
     */
    public function hasDiscount(): bool
    {
        return $this->hasPrice()
            && $this->mrp !== null
            && (float) $this->mrp > (float) $this->price;
    }

    /**
     * Rounded percentage saved off the MRP, or null when there's no discount.
     */
    public function discountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round(((float) $this->mrp - (float) $this->price) / (float) $this->mrp * 100);
    }
}
