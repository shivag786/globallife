<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_section_id', 'image_path', 'title', 'subtitle', 'short_description', 'long_description',
    'button_label', 'button_url', 'is_active', 'sort_order',
])]
class BusinessSectionCard extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<BusinessSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(BusinessSection::class, 'business_section_id');
    }
}
