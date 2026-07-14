<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

class RbacBoundariesTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_commission_partner_cannot_open_branch_dashboard(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        $this->actingAs($partner)->get('/branch/dashboard')->assertForbidden();
    }

    public function test_commission_partner_cannot_open_admin_revenue(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        $this->actingAs($partner)->get('/admin/revenue')->assertForbidden();
    }

    public function test_branch_manager_cannot_open_commission_partner_vip_members(): void
    {
        $branch = $this->makeBranchManager(30);

        $this->actingAs($branch)->get('/manager/vip-members')->assertForbidden();
    }

    public function test_commission_partner_can_open_its_own_revenue_page(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        $this->actingAs($partner)->get('/manager/revenue')->assertOk();
    }

    public function test_blocked_account_is_logged_out(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $partner->update(['status' => 'blocked']);

        // EnsureAccountIsActive logs the user out and bounces them to login.
        $this->actingAs($partner)->get('/manager/dashboard')->assertRedirect('/login');
    }
}
