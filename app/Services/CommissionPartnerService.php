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
     * Cities are not assigned here: a partner picks one up when they register a
     * VIP Member in it, so their territory follows the work they actually do.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPartner(array $data, float $commissionPercentage, User $branchManager): User
    {
        return DB::transaction(function () use ($data, $commissionPercentage, $branchManager) {
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

            return $partner;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePartner(User $partner, array $data, float $commissionPercentage): User
    {
        return DB::transaction(function () use ($partner, $data, $commissionPercentage) {
            $partner->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'commission_percentage' => $commissionPercentage,
            ]);

            return $partner;
        });
    }

    public function toggleStatus(User $partner): User
    {
        $partner->update(['status' => $partner->status === 'active' ? 'blocked' : 'active']);

        return $partner;
    }
}
