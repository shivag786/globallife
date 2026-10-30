<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A card section the business owner builds themselves on the page builder,
 * e.g. "Meet Our Doctors" or "Facilities". Its place on the page is the key
 * "custom-{id}" in VipMicrosite::page_layout['order'].
 */
#[Fillable([
    'vip_microsite_id', 'eyebrow', 'title', 'subtitle', 'style', 'is_active', 'sort_order',
])]
class BusinessSection extends Model
{
    public const STYLES = ['cards', 'people'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<VipMicrosite, $this>
     */
    public function vipMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class);
    }

    /**
     * @return HasMany<BusinessSectionCard, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(BusinessSectionCard::class)->orderBy('sort_order');
    }

    public function layoutKey(): string
    {
        return 'custom-'.$this->id;
    }
}
