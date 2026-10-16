<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Sub Admin accounts: the level between Super Admin and Branch Manager.
 *
 * A Sub Admin has no commission of its own — it is a management layer, not a
 * beneficiary, so nothing here touches `commission_percentage` and the
 * product-sale split still walks past it to the Branch Manager.
 */
class SubAdminService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createSubAdmin(array $data, User $owner): User
    {
        return DB::transaction(function () use ($data, $owner) {
            $subAdmin = User::create([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'created_by' => $owner->id,
                'email_verified_at' => now(),
            ]);

            $subAdmin->assignRole('sub_admin');

            return $subAdmin;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSubAdmin(User $subAdmin, array $data): User
    {
        return DB::transaction(function () use ($subAdmin, $data) {
            $subAdmin->update([
                'name' => $data['name'],
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'],
            ]);

            return $subAdmin;
        });
    }

    /**
     * Set a Sub Admin's password on their behalf.
     *
     * Same treatment as the Branch Manager reset: a reset usually answers a lost
     * or compromised login, so stored sessions are dropped and the remember-me
     * token rotated, or an open browser would keep working with the password
     * that was just replaced.
     */
    public function setPassword(User $subAdmin, string $password): User
    {
        return DB::transaction(function () use ($subAdmin, $password) {
            $subAdmin->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('sessions')->where('user_id', $subAdmin->id)->delete();

            return $subAdmin;
        });
    }

    public function toggleStatus(User $subAdmin): User
    {
        $subAdmin->update(['status' => $subAdmin->status === 'active' ? 'blocked' : 'active']);

        return $subAdmin;
    }
}
