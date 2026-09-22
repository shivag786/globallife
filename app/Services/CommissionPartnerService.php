<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CommissionPartnerService
{
    public function __construct(private readonly CityDirectoryService $cities) {}

    /**
     * Turn the picker's payload into city ids, creating any city that was typed
     * rather than chosen, and making sure the Branch Manager's branch covers
     * every one of them.
     *
     * A partner may only serve cities inside their Branch Manager's branch, so a
     * city picked from another state - or invented on the spot - is attached to
     * that branch here. Assigning a partner is therefore also how a Branch
     * Manager takes on a new city.
     *
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    public function resolveCities(array $data, User $branchManager): array
    {
        $ids = collect($data['cities'] ?? [])->map(fn ($id) => (int) $id);

        foreach ($data['new_cities'] ?? [] as $row) {
            if (blank($row['name'] ?? null) || blank($row['state'] ?? null)) {
                continue;
            }

            $ids->push($this->cities->resolveOrCreate($row['name'], $row['state'])->id);
        }

        $ids = $ids->unique()->values();

        $branchManager->branchCities()->syncWithoutDetaching($ids->all());

        return $ids->all();
    }

    /**
     * Create a Commission Partner account owned by the given Branch Manager.
     *
     * @param  array<string, mixed>  $data
     * @param  list<int>  $cityIds
     */
    public function createPartner(array $data, array $cityIds, float $commissionPercentage, User $branchManager): User
    {
        return DB::transaction(function () use ($data, $cityIds, $commissionPercentage, $branchManager) {
            $partner = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'commission_percentage' => $commissionPercentage,
                'created_by' => $branchManager->id,
                'email_verified_at' => now(),
            ]);

            $partner->assignRole('commission_partner');
            $partner->cities()->sync($cityIds);

            return $partner;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $cityIds
     */
    public function updatePartner(User $partner, array $data, array $cityIds, float $commissionPercentage): User
    {
        return DB::transaction(function () use ($partner, $data, $cityIds, $commissionPercentage) {
            $partner->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'commission_percentage' => $commissionPercentage,
            ]);

            $partner->cities()->sync($cityIds);

            return $partner;
        });
    }

    public function toggleStatus(User $partner): User
    {
        $partner->update(['status' => $partner->status === 'active' ? 'blocked' : 'active']);

        return $partner;
    }
}
