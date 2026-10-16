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
     * commission percentage (up to, but never over, 100%).
     *
     * `$owner` is whoever created them — a Sub Admin, or Super Admin working
     * directly — and is recorded on `created_by`, the same chain that already
     * links Commission Partners to their Branch Manager and VIP Members to their
     * Partner. It is what scopes a Sub Admin to their own Branch Managers.
     *
     * @param  array<string, mixed>  $data
     * @param  list<int>  $cityIds
     */
    public function createBranchManager(array $data, array $cityIds, float $commissionPercentage, ?User $owner = null): User
    {
        return DB::transaction(function () use ($data, $cityIds, $commissionPercentage, $owner) {
            $manager = User::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'commission_percentage' => $commissionPercentage,
                'created_by' => $owner?->id,
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

            // Only Super Admin is shown the owner field, and only they can move a
            // Branch Manager between Sub Admins; `null` hands them back to Super
            // Admin. A key that was never submitted leaves ownership untouched.
            if (array_key_exists('created_by', $data)) {
                $manager->update(['created_by' => $data['created_by'] ?: null]);
            }

            $manager->branchCities()->sync($cityIds);

            return $manager;
        });
    }

    /**
     * Set a Branch Manager's password on their behalf.
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
