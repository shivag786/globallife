<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Every role dashboard must render with the shared stat-tile component and link
 * its boxes to real routes (a bad route() call would throw at render time).
 */
class PanelDashboardsRenderTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    public function test_commission_partner_dashboard_renders_with_linked_tiles(): void
    {
        $this->seedRoles();
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSee('Total Earned')
            ->assertSee(route('manager.vip-members.index'), false)
            ->assertSee(route('manager.revenue.index'), false)
            // Revenue-flow panel: VIP + product streams, linked to revenue and wallet.
            ->assertSee('Revenue from Products')
            ->assertSee(route('wallet.index'), false);
    }

    public function test_branch_manager_dashboard_renders_with_linked_tiles(): void
    {
        $this->seedRoles();
        $bm = $this->makeBranchManager(30);

        $this->actingAs($bm)->get(route('branch.dashboard'))
            ->assertOk()
            ->assertSee('Commission Partners')
            ->assertSee(route('branch.commission-partners.index'), false)
            ->assertSee(route('branch.revenue.index'), false)
            // Revenue-flow panel: VIP + product streams, linked to revenue and wallet.
            ->assertSee('Revenue from Products')
            ->assertSee(route('wallet.index'), false);
    }

    public function test_vip_member_dashboard_renders_with_linked_tiles(): void
    {
        $this->seedRoles();
        [$vip] = $this->makeVipMember(
            $this->makeCommissionPartner($this->makeBranchManager(30), 25),
            $this->makePlan(999),
            $this->makeCity(),
        );

        $this->actingAs($vip)->get(route('vip.dashboard'))
            ->assertOk()
            ->assertSee('Total Leads')
            ->assertSee(route('vip.leads.index'), false)
            ->assertSee(route('vip.reviews.index'), false);
    }
}
