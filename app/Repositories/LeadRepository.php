<?php

namespace App\Repositories;

use App\Models\City;
use App\Models\Lead;
use App\Models\User;
use App\Models\VipMicrosite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class LeadRepository
{
    /** New (unseen) leads first, then newest first. */
    public function allPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Lead::with(['interestedPlan', 'assignedManager', 'product'])
            ->orderByRaw('seen_at IS NULL DESC')
            ->latest()
            ->latest('id')
            ->paginate($perPage);
    }

    public function unseenCount(): int
    {
        return Lead::unseen()->count();
    }

    /**
     * Unseen leads for the dashboard, newest first.
     *
     * @return \Illuminate\Support\Collection<int, Lead>
     */
    public function latestUnseen(int $limit = 10)
    {
        return Lead::unseen()->with('product')->latest()->latest('id')->limit($limit)->get();
    }

    /**
     * What the admin lead alert polls for: leads created after `$sinceId`.
     *
     * @return array{new: int, latest_id: int, unseen: int, names: list<string>}
     */
    public function pollSince(int $sinceId): array
    {
        $fresh = Lead::where('id', '>', $sinceId)->orderBy('id')->get(['id', 'name']);

        return [
            'new' => $fresh->count(),
            'latest_id' => (int) ($fresh->max('id') ?? $sinceId),
            'unseen' => $this->unseenCount(),
            'names' => $fresh->pluck('name')->take(3)->values()->all(),
        ];
    }

    public function markSeen(Lead $lead): void
    {
        if ($lead->seen_at === null) {
            $lead->forceFill(['seen_at' => now()])->saveQuietly();
        }
    }

    public function forManager(User $manager, int $perPage = 20): LengthAwarePaginator
    {
        return Lead::with('interestedPlan')
            ->where('assigned_manager_id', $manager->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Lead
    {
        $lead = Lead::create($data);

        if ($lead->vip_microsite_id) {
            // A microsite lead always goes straight to the Commission Partner who
            // owns that VIP member — no city round-robin, the owner is already known.
            $this->assignToMicrositeOwner($lead);
        } elseif ($lead->city) {
            $this->autoAssignManager($lead);
        }

        return $lead;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead;
    }

    private function assignToMicrositeOwner(Lead $lead): void
    {
        $microsite = VipMicrosite::with('user')->find($lead->vip_microsite_id);

        if ($microsite?->user?->created_by) {
            $lead->update(['assigned_manager_id' => $microsite->user->created_by]);
        }
    }

    /**
     * Round-robin lite: assign to whichever manager in the matching city currently has the fewest leads.
     */
    private function autoAssignManager(Lead $lead): void
    {
        $city = City::where('slug', Str::slug($lead->city))->where('status', 'active')->first();

        if (! $city) {
            return;
        }

        $manager = $city->managers()
            ->where('status', 'active')
            ->withCount('leads')
            ->orderBy('leads_count')
            ->first();

        if ($manager) {
            $lead->update(['assigned_manager_id' => $manager->id]);
        }
    }
}
