<?php

namespace App\Services;

use App\Models\City;
use App\Models\User;
use App\Models\VipMicrosite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VipMemberService
{
    /**
     * Create a VIP Member account with its public microsite, owned by the given
     * Commission Partner.
     *
     * The city comes in already resolved, because it may have been typed rather
     * than picked. Registering here is also what gives the partner that city —
     * and their Branch Manager the matching branch — so territory follows the
     * work instead of being assigned up front on a form.
     *
     * @param  array<string, mixed>  $data
     */
    public function createMember(array $data, User $commissionPartner, City $city): User
    {
        return DB::transaction(function () use ($data, $commissionPartner, $city) {
            $member = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'created_by' => $commissionPartner->id,
                'email_verified_at' => now(),
            ]);

            $member->assignRole('vip_member');

            VipMicrosite::create([
                'user_id' => $member->id,
                'city_id' => $city->id,
                'vip_plan_id' => $data['vip_plan_id'],
                'business_name' => $data['business_name'],
                'business_slug' => Str::slug($data['business_name']),
                'description' => $data['description'] ?? null,
                'secure_token' => Str::upper(Str::random(7)),
                'status' => 'active',
            ]);

            $commissionPartner->cities()->syncWithoutDetaching([$city->id]);

            $branchManager = $commissionPartner->creator;
            if ($branchManager?->hasRole('branch_manager')) {
                $branchManager->branchCities()->syncWithoutDetaching([$city->id]);
            }

            return $member;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateMember(User $member, array $data): User
    {
        return DB::transaction(function () use ($member, $data) {
            $member->update(['name' => $data['name'], 'email' => $data['email']]);

            $member->vipMicrosite->update([
                'vip_plan_id' => $data['vip_plan_id'],
                'description' => $data['description'] ?? null,
            ]);

            return $member;
        });
    }

    public function toggleStatus(User $member): User
    {
        $member->update(['status' => $member->status === 'active' ? 'blocked' : 'active']);

        return $member;
    }
}
