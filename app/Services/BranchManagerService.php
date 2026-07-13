<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BranchManagerService
{
    /**
     * Create a Branch Manager account, assign it to one or more cities, and cap its
     * commission percentage (Super Admin can grant up to, but never over, 100%).
     *
     * @param  array<string, mixed>  $data
     * @param  list<int>  $cityIds
     */
    public function createBranchManager(array $data, array $cityIds, float $commissionPercentage): User
    {
        return DB::transaction(function () use ($data, $cityIds, $commissionPercentage) {
            $manager = User::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'commission_percentage' => $commissionPercentage,
                'email_verified_at' => now(),
            ]);

            $manager->assignRole('branch_manager');
            $manager->branchCities()->sync($cityIds);

            return $manager;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $cityIds
     */
    public function updateBranchManager(User $manager, array $data, array $cityIds, float $commissionPercentage): User
    {
        return DB::transaction(function () use ($manager, $data, $cityIds, $commissionPercentage) {
            $manager->update([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'],
                'commission_percentage' => $commissionPercentage,
            ]);

            $manager->branchCities()->sync($cityIds);

            return $manager;
        });
    }

    public function toggleStatus(User $manager): User
    {
        $manager->update(['status' => $manager->status === 'active' ? 'blocked' : 'active']);

        return $manager;
    }
}
