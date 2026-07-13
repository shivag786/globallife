<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vip_microsite_id', 'youtube_url', 'thumbnail_url', 'title', 'sort_order', 'is_visible'])]
class BusinessVideo extends Model
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

    /**
     * Extracts the 11-character YouTube video id from any common URL shape
     * (watch?v=, youtu.be/, embed/), or null if it doesn't look like YouTube.
     */
    public static function extractYoutubeId(string $url): ?string
    {
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
