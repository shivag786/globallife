<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventRepository
{
    /**
     * @return Collection<int, Event>
     */
    public function allOrdered(): Collection
    {
        return Event::orderByDesc('event_date')->get();
    }

    public function publishedPaginated(int $perPage = 9): LengthAwarePaginator
    {
        return Event::where('status', 'active')->orderBy('event_date')->paginate($perPage);
    }

    /**
     * @return Collection<int, Event>
     */
    public function upcoming(int $limit = 3): Collection
    {
        return Event::where('status', 'active')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event;
    }
}
