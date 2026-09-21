<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Models\VipRenewal;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Renewals open RENEWAL_WINDOW_DAYS before expiry, not only after it, so a
 * member's page never has to go dark while payment is collected. Renewing early
 * must stack onto the days still owed rather than forfeiting them.
 */
class VipRenewalWindowTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function package(string $slug): VipPlan
    {
        return VipPlan::where('slug', $slug)->sole();
    }

    /**
     * A live, activated member whose plan expires in $daysOut days.
     *
     * @return array{0: User, 1: User, 2: VipMicrosite}
     */
    private function memberExpiringIn(int $daysOut, string $slug = 'growth'): array
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->package($slug), $this->makeCity());

        app(VipActivationService::class)->activate($microsite, $partner);
        $microsite->refresh()->update(['plan_expires_at' => now()->addDays($daysOut)->endOfDay()]);

        return [$partner, $member, $microsite->refresh()];
    }

    public function test_the_window_opens_thirty_days_before_expiry(): void
    {
        $this->assertSame(30, VipMicrosite::RENEWAL_WINDOW_DAYS);

        [, , $farOut] = $this->memberExpiringIn(45);
        $this->assertFalse($farOut->isRenewalDue(), '45 days out is outside the window');
        $this->assertFalse($farOut->isExpiringSoon());

        [, , $dueSoon] = $this->memberExpiringIn(20);
        $this->assertTrue($dueSoon->isRenewalDue(), '20 days out is inside the window');
        $this->assertTrue($dueSoon->isExpiringSoon());
        $this->assertFalse($dueSoon->hasExpiredPlan(), 'still live, so the page stays up');

        [, , $lapsed] = $this->memberExpiringIn(-3);
        $this->assertTrue($lapsed->isRenewalDue());
        $this->assertTrue($lapsed->hasExpiredPlan());
        $this->assertFalse($lapsed->isExpiringSoon(), 'expired is not "expiring soon"');
    }

    public function test_the_list_offers_renewal_inside_the_window_but_not_before(): void
    {
        [$partnerA, $memberA] = $this->memberExpiringIn(45);
        $this->actingAs($partnerA)->get('/manager/vip-members')
            ->assertOk()
            ->assertSee('Valid till')
            ->assertDontSee("/manager/vip-members/{$memberA->id}/renewal", false);

        [$partnerB, $memberB] = $this->memberExpiringIn(20);
        $this->actingAs($partnerB)->get('/manager/vip-members')
            ->assertOk()
            ->assertSee('Renew early')
            ->assertSee('20 days left')
            ->assertSee("/manager/vip-members/{$memberB->id}/renewal", false);
    }

    public function test_the_renewal_screen_opens_inside_the_window(): void
    {
        [$partner, $member] = $this->memberExpiringIn(10);

        $this->actingAs($partner)->get("/manager/vip-members/{$member->id}/renewal")
            ->assertOk()
            ->assertSee('Expires', false)
            ->assertSee('page still live', false);
    }

    public function test_the_renewal_screen_stays_shut_outside_the_window(): void
    {
        [$partner, $member] = $this->memberExpiringIn(60);

        $this->actingAs($partner)->get("/manager/vip-members/{$member->id}/renewal")
            ->assertRedirect(route('manager.vip-members.index'))
            ->assertSessionHas('error');

        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('premium')->id],
        )->assertSessionHas('error');

        $this->assertSame(0, VipRenewal::count());
    }

    public function test_renewing_early_adds_the_new_term_on_top_of_the_days_left(): void
    {
        [$partner, $member, $microsite] = $this->memberExpiringIn(20);
        $expiryBefore = $microsite->plan_expires_at->copy();

        // Growth is 3 months, so this must land 3 months after the CURRENT expiry.
        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('growth')->id],
        )->assertSessionHas('status');

        $microsite->refresh();

        $this->assertSame(
            $expiryBefore->copy()->addMonths(3)->toDateString(),
            $microsite->plan_expires_at->toDateString(),
            'the 20 remaining days must not be forfeited',
        );
        $this->assertTrue($microsite->plan_expires_at->greaterThan(now()->addMonths(3)));
    }

    public function test_renewing_after_expiry_runs_the_term_from_today(): void
    {
        [$partner, $member, $microsite] = $this->memberExpiringIn(-40);

        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('growth')->id],
        )->assertSessionHas('status');

        // Not back-dated to the lapsed expiry — a full cycle from today.
        $this->assertSame(
            now()->addMonths(3)->toDateString(),
            $microsite->refresh()->plan_expires_at->toDateString(),
        );
    }

    public function test_the_member_is_warned_before_their_page_goes_dark(): void
    {
        [, $member, $microsite] = $this->memberExpiringIn(12);

        $this->actingAs($member)->get('/vip/dashboard')
            ->assertOk()
            ->assertSee('12 days left', false)
            ->assertSee('never goes offline', false);

        // The public page is untouched while the plan is still live.
        $this->get($microsite->publicPath())->assertOk();
    }
}
