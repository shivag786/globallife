<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    /**
     * Set a Commission Partner's password on their Branch Manager's behalf.
     *
     * A reset usually answers a lost or compromised login, so the old
     * credentials are cut off properly: every stored session for that account is
     * dropped and the remember-me token rotated, or an open browser would keep
     * working with the password that was just replaced.
     */
    public function setPassword(User $partner, string $password): User
    {
        return DB::transaction(function () use ($partner, $password) {
            $partner->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            // The session driver is `database`, so this is where a live login lives.
            DB::table('sessions')->where('user_id', $partner->id)->delete();

            return $partner;
        });
    }

    public function toggleStatus(User $partner): User
    {
        $partner->update(['status' => $partner->status === 'active' ? 'blocked' : 'active']);

        return $partner;
    }
}
