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
    public function allPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Lead::with(['interestedPlan', 'assignedManager'])->latest()->paginate($perPage);
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
