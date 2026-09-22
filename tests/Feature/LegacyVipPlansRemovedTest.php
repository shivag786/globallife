<?php

namespace Tests\Feature;

use App\Models\CommissionTransaction;
use App\Models\User;
use App\Models\VipPlan;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Only the four packages remain, and the VIP Plans admin is gone.
 *
 * Deleting the legacy plans meant letting history give up its plan link:
 * `commission_transactions.vip_plan_id` and `vip_renewals.vip_plan_id` are now
 * nullable with ON DELETE SET NULL. The money facts must survive that.
 */
class LegacyVipPlansRemovedTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_only_the_four_packages_exist_and_the_legacy_plans_are_gone(): void
    {
        $this->assertSame(
            ['growth', 'professional', 'growth-plus', 'premium'],
            VipPlan::orderBy('display_order')->pluck('slug')->all(),
        );

        $this->assertSame(0, VipPlan::whereIn('slug', [
            'silver-vip', 'gold-vip', 'platinum-vip', 'diamond-vip',
        ])->count());
    }

    public function test_the_admin_vip_plans_screens_no_longer_exist(): void
    {
        $this->assertFalse(
            Route::has('admin.vip-plans.index'),
            'the admin VIP Plans routes should be gone',
        );

        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/vip-plans')->assertNotFound();
        $this->actingAs($admin)->get('/admin/vip-plans/create')->assertNotFound();

        // And nothing in the panel offers a way there.
        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Active VIP Plans', false)
            ->assertDontSee('Manage plans', false)
            ->assertDontSee('/admin/vip-plans', false);
    }

    public function test_commission_history_keeps_its_money_when_its_plan_is_deleted(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);

        // A throwaway plan standing in for a legacy one.
        $doomed = $this->makePlan(999);
        [, $microsite] = $this->makeVipMember($partner, $doomed, $this->makeCity());

        $transaction = app(VipActivationService::class)->activate($microsite, $partner);
        $this->assertSame($doomed->id, $transaction->vip_plan_id);

        // Move the microsite off it, exactly as the migration does, then delete.
        $microsite->refresh()->update(['vip_plan_id' => VipPlan::where('slug', 'growth')->value('id')]);
        $doomed->delete();

        $transaction->refresh();

        // The link is gone...
        $this->assertNull($transaction->vip_plan_id);
        $this->assertNull($transaction->vipPlan);

        // ...but every figure that matters is untouched.
        $this->assertEqualsWithDelta(999, (float) $transaction->package_amount, 0.01);
        $this->assertEqualsWithDelta(249.75, (float) $transaction->commission_partner_amount, 0.01);
        $this->assertEqualsWithDelta(699.30, (float) $transaction->company_amount, 0.01);
        $this->assertSame(1, CommissionTransaction::count());
    }

    public function test_the_revenue_report_still_renders_with_a_nulled_plan(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $doomed = $this->makePlan(999);
        [, $microsite] = $this->makeVipMember($partner, $doomed, $this->makeCity());

        app(VipActivationService::class)->activate($microsite, $partner);
        $microsite->refresh()->update(['vip_plan_id' => VipPlan::where('slug', 'growth')->value('id')]);
        $doomed->delete();

        // A blank package must not take the page down.
        $this->actingAs($this->superAdmin())->get('/admin/revenue')
            ->assertOk()
            ->assertSee($microsite->business_name, false);
    }

    public function test_a_microsite_always_keeps_a_real_plan_so_its_caps_still_work(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $doomed = $this->makePlan(999);
        [, $microsite] = $this->makeVipMember($partner, $doomed, $this->makeCity());

        $growth = VipPlan::where('slug', 'growth')->sole();
        $microsite->update(['vip_plan_id' => $growth->id]);
        $doomed->delete();

        $microsite->refresh()->load('vipPlan');

        $this->assertNotNull($microsite->vipPlan, 'a microsite must never be left without a plan');
        // Legacy plans carried 0 caps, which blocked members entirely; the
        // replacement gives them the real package limits.
        $this->assertSame(15, $microsite->contentQuota('products')['limit']);
        $this->assertSame(6, $microsite->contentQuota('services')['limit']);
    }
}
