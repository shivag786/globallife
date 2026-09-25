<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    /**
     * Set a Branch Manager's password on their behalf (Super Admin only).
     *
     * A reset is usually a response to a lost or compromised login, so the old
     * credentials are cut off properly: every stored session for that account is
     * dropped and the remember-me token is rotated, otherwise an existing browser
     * would keep working with the password that was just replaced.
     */
    public function setPassword(User $manager, string $password): User
    {
        return DB::transaction(function () use ($manager, $password) {
            $manager->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            // The session driver is `database`, so this is where a live login lives.
            DB::table('sessions')->where('user_id', $manager->id)->delete();

            return $manager;
        });
    }

    public function toggleStatus(User $manager): User
    {
        $manager->update(['status' => $manager->status === 'active' ? 'blocked' : 'active']);

        return $manager;
    }
}
