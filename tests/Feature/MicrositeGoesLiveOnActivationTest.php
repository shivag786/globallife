<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VipMicrosite;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A microsite goes public at activation, not at creation.
 *
 * Adding a member through the form now activates them in the same breath (see
 * VipMemberActivatesOnCreationTest), but an unactivated microsite still has a
 * NULL expiry and so is not "expired" — which is exactly the hole this covers.
 * These build the microsite directly, the way rows predating auto-activation
 * look, and prove such a page is a draft rather than something already serving
 * to the public.
 */
class MicrositeGoesLiveOnActivationTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * A member whose plan has NOT been activated — exactly what adding one gives.
     *
     * @return array{0: User, 1: User, 2: VipMicrosite}
     */
    private function unactivatedMember(): array
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->makePlan(1000), $this->makeCity());

        return [$partner, $member, $microsite];
    }

    public function test_a_new_member_is_not_activated_and_is_not_live(): void
    {
        [, , $microsite] = $this->unactivatedMember();

        $this->assertFalse($microsite->isActivated());
        $this->assertFalse($microsite->hasExpiredPlan(), 'no expiry yet, so not expired either');
        $this->assertFalse($microsite->isLive(), 'but it must still not be public');
    }

    public function test_the_public_page_shows_the_maintenance_notice_before_activation(): void
    {
        [, , $microsite] = $this->unactivatedMember();

        $response = $this->get($microsite->publicPath());

        $response->assertStatus(503);
        $response->assertSee('Under Maintenance', false);
        $response->assertDontSee('Write a Review', false);

        // A maintenance hit is not a profile view.
        $this->assertSame(0, $microsite->events()->where('event_type', 'page_view')->count());
    }

    public function test_activating_puts_the_page_live(): void
    {
        [$partner, , $microsite] = $this->unactivatedMember();

        $this->get($microsite->publicPath())->assertStatus(503);

        app(VipActivationService::class)->activate($microsite, $partner);
        $microsite->refresh();

        $this->assertTrue($microsite->isLive());

        $response = $this->get($microsite->publicPath());
        $response->assertOk();
        $response->assertSee($microsite->business_name);
        $this->assertSame(1, $microsite->events()->where('event_type', 'page_view')->count());
    }

    public function test_contact_shortcuts_stay_shut_until_activation(): void
    {
        [$partner, , $microsite] = $this->unactivatedMember();
        $microsite->update(['whatsapp_number' => '9876543210']);

        // Bounced back to the page, and nothing recorded behind the notice.
        $this->get("/microsite/{$microsite->id}/click/whatsapp")
            ->assertRedirect($microsite->publicPath());
        $this->assertSame(0, $microsite->events()->where('event_type', 'whatsapp_click')->count());

        app(VipActivationService::class)->activate($microsite, $partner);

        $this->get("/microsite/{$microsite->id}/click/whatsapp")->assertRedirect();
        $this->assertSame(1, $microsite->events()->where('event_type', 'whatsapp_click')->count());
    }

    public function test_reviews_cannot_be_posted_before_activation(): void
    {
        [, , $microsite] = $this->unactivatedMember();

        $this->post($microsite->publicPath().'/reviews', [
            'customer_name' => 'Someone',
            'rating' => 5,
            'review_text' => 'Great',
        ])->assertRedirect();

        $this->assertSame(0, $microsite->reviews()->count());
    }

    public function test_the_member_is_told_their_page_is_not_live_yet(): void
    {
        [$partner, $member, $microsite] = $this->unactivatedMember();

        $this->actingAs($member)->get('/vip/dashboard')
            ->assertOk()
            ->assertSee('Your page is not live yet', false)
            ->assertSee('Commission Partner activates it', false);

        app(VipActivationService::class)->activate($microsite, $partner);

        // Once live, the notice goes away.
        $this->actingAs($member)->get('/vip/dashboard')
            ->assertOk()
            ->assertDontSee('Your page is not live yet', false);
    }

    public function test_an_expired_plan_is_still_not_live(): void
    {
        [$partner, , $microsite] = $this->unactivatedMember();
        app(VipActivationService::class)->activate($microsite, $partner);

        $microsite->refresh()->update(['plan_expires_at' => now()->subDay()]);

        $this->assertTrue($microsite->isActivated());
        $this->assertFalse($microsite->fresh()->isLive());
        $this->get($microsite->publicPath())->assertStatus(503);
    }
}
