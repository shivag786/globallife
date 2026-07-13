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
}
