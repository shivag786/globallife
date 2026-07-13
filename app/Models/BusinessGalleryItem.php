<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vip_microsite_id', 'image_path', 'title', 'sort_order', 'is_visible'])]
class BusinessGalleryItem extends Model
{
    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    /**
     * @return BelongsTo<VipMicrosite, $this>
     */
    public function vipMicrosite(): BelongsTo
    {
        return $this->belongsTo(VipMicrosite::class);
    }
}
