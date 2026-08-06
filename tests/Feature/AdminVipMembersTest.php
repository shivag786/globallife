<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The Super Admin VIP members oversight page must show each member's details,
 * plan renewal, upline (CP + Branch Manager) with their commission, and a link
 * to the live microsite.
 */
class AdminVipMembersTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    public function test_lists_vip_member_with_upline_commission_and_site_link(): void
    {
        $this->seedRoles();

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        $bm = $this->makeBranchManager(30);
        $cp = $this->makeCommissionPartner($bm, 25);
        [$vip, $microsite] = $this->makeVipMember($cp, $this->makePlan(1000), $this->makeCity());

        // Activate → creates the CommissionTransaction with the split + activated_at.
        app(VipActivationService::class)->activate($microsite->fresh(), $admin);

        $response = $this->actingAs($admin)->get(route('admin.vip-members.index'));

        $response->assertOk()
            ->assertSee($microsite->business_name)
            ->assertSee($vip->name)
            ->assertSee($cp->name)
            ->assertSee($bm->name)
            // Renewal is one year after activation.
            ->assertSee(now()->addYear()->format('d M Y'))
            // Visit-site link points at the public microsite path.
            ->assertSee($microsite->fresh()->publicPath(), false);
    }
}
