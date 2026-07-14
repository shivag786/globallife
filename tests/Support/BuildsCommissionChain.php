<?php

namespace Tests\Support;

use App\Models\City;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;

/**
 * Helpers to assemble the Branch Manager → Commission Partner → VIP Member chain
 * used by the commission/revenue tests.
 */
trait BuildsCommissionChain
{
    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        // Clear spatie's permission cache so freshly-seeded roles resolve within the test.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function makeBranchManager(float $percentage): User
    {
        $user = User::factory()->create(['status' => 'active', 'commission_percentage' => $percentage]);
        $user->assignRole('branch_manager');

        return $user;
    }

    protected function makeCommissionPartner(User $branchManager, float $percentage): User
    {
        $user = User::factory()->create([
            'status' => 'active',
            'commission_percentage' => $percentage,
            'created_by' => $branchManager->id,
        ]);
        $user->assignRole('commission_partner');

        return $user;
    }

    /**
     * @return array{0: User, 1: VipMicrosite}
     */
    protected function makeVipMember(User $commissionPartner, VipPlan $plan, City $city): array
    {
        $member = User::factory()->create(['status' => 'active', 'created_by' => $commissionPartner->id]);
        $member->assignRole('vip_member');

        $microsite = VipMicrosite::create([
            'user_id' => $member->id,
            'city_id' => $city->id,
            'vip_plan_id' => $plan->id,
            'business_name' => 'Biz '.Str::random(5),
            'business_slug' => 'biz-'.Str::lower(Str::random(8)),
            'secure_token' => Str::upper(Str::random(7)),
            'status' => 'active',
        ]);

        return [$member, $microsite];
    }

    protected function makePlan(float $joiningPrice): VipPlan
    {
        return VipPlan::create([
            'name' => 'Plan '.Str::random(4),
            'slug' => 'plan-'.Str::lower(Str::random(8)),
            'monthly_price' => 0,
            'yearly_price' => 0,
            'renewal_price' => 0,
            'joining_price' => $joiningPrice,
            'status' => 'active',
        ]);
    }

    protected function makeCity(): City
    {
        $name = 'City '.Str::random(5);

        return City::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'state' => 'Test State',
            'status' => 'active',
        ]);
    }
}
