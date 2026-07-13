<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vip_microsite_id', 'customer_name', 'photo_path', 'rating', 'review_text', 'review_date', 'is_verified', 'is_featured', 'status'])]
class BusinessReview extends Model
{
    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'is_verified' => 'boolean',
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
