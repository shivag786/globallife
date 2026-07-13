<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CommissionPartnerService
{
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
