<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CommissionTransaction;
use App\Models\User;
use App\Models\VipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Adding a VIP Member activates their plan there and then.
 *
 * A Commission Partner only fills that form once the joining fee is in hand, so
 * making them press Activate afterwards just left pages dark and commission
 * unbooked. Creation and activation are now one transaction: either the member
 * exists with their page live and the split recorded, or nothing happened.
 *
 * The manual Activate button survives for accounts created before this change.
 */
class VipMemberActivatesOnCreationTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function plan(): VipPlan
    {
        return VipPlan::where('slug', 'growth')->sole();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Member',
            'email' => 'new.member@example.com',
            'password' => 'a-strong-password',
            'vip_plan_id' => $this->plan()->id,
            'business_name' => 'Lifeline Clinic',
            'new_city' => ['name' => 'Orchha', 'state' => 'Madhya Pradesh'],
        ], $overrides);
    }

    private function member(): ?User
    {
        return User::where('email', 'new.member@example.com')->first();
    }

    public function test_creating_a_member_puts_their_page_live_immediately(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $plan = $this->plan();

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())
            ->assertRedirect(route('manager.vip-members.index'));

        $microsite = $this->member()->vipMicrosite;

        $this->assertTrue($microsite->isActivated());
        $this->assertTrue($microsite->isLive(), 'the page must be public without a second step');
        $this->assertNotNull($microsite->plan_expires_at);
        $this->assertSame(
            now()->addMonths($plan->validityMonths())->toDateString(),
            $microsite->plan_expires_at->toDateString(),
            'the paid cycle starts at creation',
        );
    }

    public function test_the_public_page_serves_straight_away(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())->assertRedirect();

        $microsite = $this->member()->vipMicrosite;

        $this->get($microsite->publicPath())
            ->assertOk()
            ->assertSee($microsite->business_name);
    }

    public function test_the_commission_split_is_booked_at_creation(): void
    {
        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 25);
        $package = (float) $this->plan()->joining_price;

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())->assertRedirect();

        $transaction = CommissionTransaction::where('vip_microsite_id', $this->member()->vipMicrosite->id)->sole();

        $this->assertSame($partner->id, $transaction->commission_partner_id);
        $this->assertSame($branch->id, $transaction->branch_manager_id);
        $this->assertSame($partner->id, $transaction->activated_by, 'the partner who added them activated them');

        // 25% partner, the unused 5% of the branch cap, 70% company.
        $this->assertEquals(round($package * 0.25, 2), (float) $transaction->commission_partner_amount);
        $this->assertEquals(round($package * 0.05, 2), (float) $transaction->branch_manager_amount);
        $this->assertEquals(round($package * 0.70, 2), (float) $transaction->company_amount);
    }

    public function test_the_partner_is_told_the_member_is_live_and_the_commission_recorded(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())
            ->assertSessionHas('status', fn (string $status) => str_contains($status, 'activated')
                && str_contains($status, 'commission'));
    }

    public function test_a_new_member_is_offered_no_activate_button(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())->assertRedirect();

        $this->actingAs($partner)->get('/manager/vip-members')
            ->assertOk()
            ->assertSee('Activated', false)
            ->assertDontSee('Yes, activate', false);
    }

    public function test_a_rejected_form_creates_nothing_and_books_no_commission(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        // No city given at all.
        $this->actingAs($partner)
            ->post('/manager/vip-members', $this->payload(['new_city' => null]))
            ->assertSessionHasErrors('new_city.name');

        $this->assertNull($this->member());
        $this->assertSame(0, CommissionTransaction::count());
    }

    public function test_activation_is_booked_once_only(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($partner)->post('/manager/vip-members', $this->payload())->assertRedirect();

        // The legacy endpoint must not be a way to double-book the split.
        $member = $this->member();
        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/activate")
            ->assertSessionHas('error');

        $this->assertSame(1, CommissionTransaction::count());
    }

    public function test_members_created_before_this_change_can_still_be_activated_by_hand(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $city = City::create([
            'name' => 'Jhansi',
            'slug' => 'jhansi-'.Str::lower(Str::random(4)),
            'state' => 'Uttar Pradesh',
            'status' => 'active',
        ]);

        // Built the old way: account and microsite, no activation.
        [$legacy, $microsite] = $this->makeVipMember($partner, $this->plan(), $city);
        $this->assertFalse($microsite->isActivated());

        $this->actingAs($partner)->get('/manager/vip-members')
            ->assertOk()
            ->assertSee('Yes, activate', false);

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$legacy->id}/activate")
            ->assertSessionHas('status');

        $this->assertTrue($microsite->refresh()->isLive());
    }
}
