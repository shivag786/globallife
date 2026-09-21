<?php

namespace Tests\Feature;

use App\Models\CommissionTransaction;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipRenewal;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * VIP plan expiry + the Commission Partner's approve/reject renewal flow, and
 * the maintenance notice an expired microsite serves to the public.
 */
class VipPlanRenewalTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    /**
     * Activate a member and drag their plan past its expiry date.
     *
     * @return array{0: User, 1: User, 2: VipMicrosite}
     */
    private function expiredMember(): array
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        app(VipActivationService::class)->activate($microsite, $partner);
        $microsite->refresh()->update(['plan_expires_at' => now()->subDay()]);

        return [$partner, $member, $microsite->refresh()];
    }

    public function test_activation_starts_a_paid_cycle_from_the_plans_validity(): void
    {
        $this->seedRoles();

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $plan = $this->makePlan(1000);
        $plan->update(['validity_months' => 6]);
        [, $microsite] = $this->makeVipMember($partner, $plan, $this->makeCity());

        $this->assertNull($microsite->plan_expires_at);
        $this->assertFalse($microsite->hasExpiredPlan(), 'A never-activated microsite must not count as expired.');

        app(VipActivationService::class)->activate($microsite, $partner);
        $microsite->refresh();

        $this->assertNotNull($microsite->plan_expires_at);
        $this->assertSame(now()->addMonths(6)->toDateString(), $microsite->plan_expires_at->toDateString());
        $this->assertFalse($microsite->hasExpiredPlan());
    }

    public function test_an_expired_microsite_serves_the_maintenance_notice_instead_of_the_profile(): void
    {
        $this->seedRoles();
        [, , $microsite] = $this->expiredMember();

        $response = $this->get($microsite->publicPath());

        $response->assertStatus(503);
        $response->assertSee('Under Maintenance', false);
        $response->assertDontSee('Write a Review', false);

        // A maintenance hit is not a profile view.
        $this->assertSame(0, $microsite->events()->where('event_type', 'page_view')->count());
    }

    public function test_a_live_microsite_is_unaffected(): void
    {
        $this->seedRoles();

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());
        app(VipActivationService::class)->activate($microsite, $partner);

        $response = $this->get($microsite->refresh()->publicPath());

        $response->assertOk();
        $response->assertSee($microsite->business_name);
        $this->assertSame(1, $microsite->events()->where('event_type', 'page_view')->count());
    }

    public function test_partner_sees_the_renewal_action_only_once_the_plan_expires(): void
    {
        $this->seedRoles();

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());
        app(VipActivationService::class)->activate($microsite, $partner);

        $renewalUrl = "/manager/vip-members/{$member->id}/renewal";

        // Still inside the paid cycle — no way through to the packages.
        $this->actingAs($partner)->get('/manager/vip-members')
            ->assertOk()
            ->assertSee('Valid till')
            ->assertDontSee($renewalUrl, false);

        $microsite->refresh()->update(['plan_expires_at' => now()->subDay()]);

        // Expired: the row offers the renewal screen, where Approve/Reject live.
        $this->actingAs($partner)->get('/manager/vip-members')
            ->assertOk()
            ->assertSee('Expired')
            ->assertSee($renewalUrl, false);
    }

    public function test_approving_a_renewal_restarts_the_cycle_and_brings_the_page_back(): void
    {
        $this->seedRoles();
        [$partner, $member, $microsite] = $this->expiredMember();

        $this->get($microsite->publicPath())->assertStatus(503);

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/approve", [
                'vip_plan_id' => $microsite->vip_plan_id,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $microsite->refresh();
        $this->assertFalse($microsite->hasExpiredPlan());
        $this->assertSame(now()->addMonths(12)->toDateString(), $microsite->plan_expires_at->toDateString());

        $renewal = VipRenewal::where('vip_microsite_id', $microsite->id)->sole();
        $this->assertSame('approved', $renewal->decision);
        $this->assertNotNull($renewal->new_expires_at);

        $this->get($microsite->publicPath())->assertOk()->assertSee($microsite->business_name);
    }

    public function test_rejecting_a_renewal_records_the_refusal_and_leaves_the_page_down(): void
    {
        $this->seedRoles();
        [$partner, $member, $microsite] = $this->expiredMember();
        $expiredAt = $microsite->plan_expires_at;

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/reject")
            ->assertRedirect()
            ->assertSessionHas('status');

        $microsite->refresh();
        $this->assertTrue($microsite->hasExpiredPlan());
        $this->assertSame($expiredAt->toDateTimeString(), $microsite->plan_expires_at->toDateTimeString());

        $renewal = VipRenewal::where('vip_microsite_id', $microsite->id)->sole();
        $this->assertSame('rejected', $renewal->decision);
        $this->assertNull($renewal->new_expires_at);

        $this->get($microsite->publicPath())->assertStatus(503);
    }

    public function test_a_plan_that_has_not_expired_cannot_be_renewed(): void
    {
        $this->seedRoles();

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());
        app(VipActivationService::class)->activate($microsite, $partner);
        $expiry = $microsite->refresh()->plan_expires_at;

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/approve", [
                'vip_plan_id' => $microsite->vip_plan_id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, VipRenewal::count());
        $this->assertSame($expiry->toDateTimeString(), $microsite->refresh()->plan_expires_at->toDateTimeString());
    }

    public function test_a_partner_cannot_renew_someone_elses_member(): void
    {
        $this->seedRoles();
        [, $member, $microsite] = $this->expiredMember();

        $outsider = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($outsider)
            ->patch("/manager/vip-members/{$member->id}/renewal/approve")
            ->assertForbidden();

        $this->assertSame(0, VipRenewal::count());
        $this->assertTrue($microsite->refresh()->hasExpiredPlan());
    }

    public function test_renewal_records_no_vip_activation_commission(): void
    {
        $this->seedRoles();
        [$partner, $member, $microsite] = $this->expiredMember();

        $before = CommissionTransaction::count();

        $this->actingAs($partner)->patch("/manager/vip-members/{$member->id}/renewal/approve");

        // Joining commission stays one-per-microsite; renewals pay nothing yet.
        $this->assertSame($before, CommissionTransaction::count());
    }
}
