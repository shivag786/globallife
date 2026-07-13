<?php

namespace App\Repositories;

use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Collection;

class MediaRepository
{
    /**
     * @return Collection<int, MediaItem>
     */
    public function allOrdered(): Collection
    {
        return MediaItem::orderBy('display_order')->latest()->get();
    }

    /**
     * @return Collection<int, MediaItem>
     */
    public function activeOrdered(int $limit = 12): Collection
    {
        return MediaItem::where('status', 'active')->orderBy('display_order')->limit($limit)->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MediaItem
    {
        return MediaItem::create($data);
    }

    public function toggleStatus(MediaItem $item): MediaItem
    {
        $item->update(['status' => $item->status === 'active' ? 'inactive' : 'active']);

        return $item;
    }
}
