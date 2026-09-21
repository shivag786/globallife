<?php

namespace Tests\Feature;

use App\Models\BusinessProduct;
use App\Models\BusinessService;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Models\VipRenewal;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The four renewal packages, and the product/service caps they buy.
 *
 * The cap is on the TOTAL row count, so a member with 15 products who renews on
 * Growth (15) keeps those 15 but cannot add a 16th, while Professional (35)
 * leaves room for 20 more. Nothing is ever deleted or hidden when a member ends
 * up over the cap.
 */
class VipPackageRenewalTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
        // The packages ship as a migration, which RefreshDatabase runs.
        $this->assertSame(4, VipPlan::where('status', 'active')->count());
    }

    private function package(string $slug): VipPlan
    {
        return VipPlan::where('slug', $slug)->sole();
    }

    /**
     * An expired member owned by a fresh partner.
     *
     * @return array{0: User, 1: User, 2: VipMicrosite}
     */
    private function expiredMember(string $slug = 'growth'): array
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->package($slug), $this->makeCity());

        app(VipActivationService::class)->activate($microsite, $partner);
        $microsite->refresh()->update(['plan_expires_at' => now()->subDay()]);

        return [$partner, $member, $microsite->refresh()];
    }

    private function addProducts(VipMicrosite $microsite, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            BusinessProduct::create([
                'vip_microsite_id' => $microsite->id,
                'name' => 'Product '.$i,
                'slug' => 'product-'.$i.'-'.Str::lower(Str::random(5)),
                'status' => 'published',
            ]);
        }
    }

    private function addServices(VipMicrosite $microsite, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            BusinessService::create([
                'vip_microsite_id' => $microsite->id,
                'name' => 'Service '.$i,
                'slug' => 'service-'.$i.'-'.Str::lower(Str::random(5)),
                'status' => 'published',
            ]);
        }
    }

    public function test_the_four_packages_carry_the_prices_validity_and_caps_from_the_table(): void
    {
        $expected = [
            'growth' => [4999, 3, 15, 6],
            'professional' => [14999, 6, 35, 15],
            'growth-plus' => [24999, 12, 50, 30],
            'premium' => [41999, 12, 100, 100],
        ];

        foreach ($expected as $slug => [$price, $months, $products, $services]) {
            $plan = $this->package($slug);

            $this->assertEqualsWithDelta($price, (float) $plan->renewal_price, 0.01, $slug);
            $this->assertSame($months, $plan->validityMonths(), $slug);
            $this->assertSame($products, $plan->productLimit(), $slug);
            $this->assertSame($services, $plan->serviceLimit(), $slug);
        }

        // The legacy plans are retired, so exactly four packages are offered.
        $this->assertSame(0, VipPlan::whereIn('slug', ['silver-vip', 'gold-vip'])->where('status', 'active')->count());
    }

    public function test_the_renewal_screen_shows_all_four_packages(): void
    {
        [$partner, $member] = $this->expiredMember();

        $response = $this->actingAs($partner)->get("/manager/vip-members/{$member->id}/renewal");

        $response->assertOk();
        foreach (['Growth', 'Professional', 'Growth Plus', 'Premium'] as $name) {
            $response->assertSee($name, false);
        }
        $response->assertSee('4,999', false);
        $response->assertSee('41,999', false);
        // Approve and Reject both ask for confirmation before submitting.
        $response->assertSee('data-confirm', false);
        $response->assertSee('Reject renewal', false);
    }

    public function test_approving_a_package_sets_the_validity_and_the_caps(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember();
        $professional = $this->package('professional');

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/approve", ['vip_plan_id' => $professional->id])
            ->assertRedirect()
            ->assertSessionHas('status');

        $microsite->refresh();

        $this->assertSame($professional->id, $microsite->vip_plan_id);
        $this->assertFalse($microsite->hasExpiredPlan());
        // Professional is 6 months, not the 3 of their old Growth plan.
        $this->assertSame(now()->addMonths(6)->toDateString(), $microsite->plan_expires_at->toDateString());
        $this->assertSame(35, $microsite->contentQuota('products')['limit']);
        $this->assertSame(15, $microsite->contentQuota('services')['limit']);

        $renewal = VipRenewal::where('vip_microsite_id', $microsite->id)->sole();
        $this->assertSame('approved', $renewal->decision);
        $this->assertSame($professional->id, $renewal->vip_plan_id);
        $this->assertEqualsWithDelta(14999, (float) $renewal->amount, 0.01);
    }

    public function test_growth_with_15_products_already_used_blocks_a_16th(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember();
        $this->addProducts($microsite, 15);

        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('growth')->id],
        );

        $quota = $microsite->refresh()->contentQuota('products');
        $this->assertSame(15, $quota['used']);
        $this->assertSame(15, $quota['limit']);
        $this->assertFalse($quota['can_add']);

        // The member keeps all 15 and may still edit them, but cannot add more.
        $this->actingAs($member)->get('/vip/products')->assertOk()->assertSee('15 / 15', false);

        $this->actingAs($member)->get('/vip/products/create')
            ->assertRedirect(route('vip.products.index'))
            ->assertSessionHas('error');

        $this->actingAs($member)->post('/vip/products', [
            'name' => 'Sixteenth', 'slug' => 'sixteenth', 'status' => 'published',
        ])->assertRedirect(route('vip.products.index'))->assertSessionHas('error');

        $this->assertSame(15, $microsite->products()->count());
    }

    public function test_professional_gives_15_old_plus_20_new_product_slots(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember();
        $this->addProducts($microsite, 15);

        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('professional')->id],
        );

        $quota = $microsite->refresh()->contentQuota('products');
        $this->assertSame(15, $quota['used']);
        $this->assertSame(35, $quota['limit']);
        $this->assertSame(20, $quota['remaining'], '15 already used out of 35 leaves 20 new slots');
        $this->assertTrue($quota['can_add']);

        $this->actingAs($member)->get('/vip/products/create')->assertOk();

        // Fill the remaining 20, then the 36th is refused.
        $this->addProducts($microsite, 20);
        $this->assertFalse($microsite->refresh()->contentQuota('products')['can_add']);

        $this->actingAs($member)->post('/vip/products', [
            'name' => 'Thirty Sixth', 'slug' => 'thirty-sixth', 'status' => 'published',
        ])->assertSessionHas('error');

        $this->assertSame(35, $microsite->products()->count());
    }

    public function test_services_are_capped_the_same_way(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember();
        $this->addServices($microsite, 6);

        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('growth')->id],
        );

        $this->assertFalse($microsite->refresh()->contentQuota('services')['can_add']);

        $this->actingAs($member)->post('/vip/services', [
            'name' => 'Seventh', 'slug' => 'seventh', 'status' => 'published',
        ])->assertRedirect(route('vip.services.index'))->assertSessionHas('error');

        $this->assertSame(6, $microsite->services()->count());

        // Growth Plus lifts them to 30, so adding works again.
        $microsite->update(['plan_expires_at' => now()->subDay()]);
        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('growth-plus')->id],
        );

        $this->assertTrue($microsite->refresh()->contentQuota('services')['can_add']);
        $this->assertSame(24, $microsite->contentQuota('services')['remaining']);
    }

    public function test_a_downgrade_keeps_existing_items_and_only_blocks_adding(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember('premium');
        $this->addProducts($microsite, 40);

        $this->actingAs($partner)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('growth')->id],
        );

        $quota = $microsite->refresh()->contentQuota('products');

        $this->assertSame(40, $microsite->products()->count(), 'nothing may be deleted on a downgrade');
        $this->assertTrue($quota['over']);
        $this->assertFalse($quota['can_add']);
        $this->assertSame(0, $quota['remaining']);
    }

    public function test_approving_without_a_package_is_refused(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember();

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/approve", [])
            ->assertSessionHasErrors('vip_plan_id');

        $this->assertTrue($microsite->refresh()->hasExpiredPlan());
        $this->assertSame(0, VipRenewal::count());
    }

    public function test_a_retired_plan_cannot_be_used_for_a_renewal(): void
    {
        [$partner, $member] = $this->expiredMember();

        // A retired package: still referenced by history, but not on offer.
        $retired = $this->package('premium')->replicate();
        $retired->slug = 'legacy-package';
        $retired->name = 'Legacy Package';
        $retired->status = 'inactive';
        $retired->save();

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/approve", ['vip_plan_id' => $retired->id])
            ->assertSessionHasErrors('vip_plan_id');

        $this->assertSame(0, VipRenewal::count());
    }

    public function test_rejecting_records_the_refusal_and_leaves_the_plan_expired(): void
    {
        [$partner, $member, $microsite] = $this->expiredMember();

        $this->actingAs($partner)
            ->patch("/manager/vip-members/{$member->id}/renewal/reject")
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue($microsite->refresh()->hasExpiredPlan());
        $this->assertSame('rejected', VipRenewal::sole()->decision);
    }

    public function test_the_renewal_screen_is_closed_while_the_plan_is_live(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->package('growth'), $this->makeCity());
        app(VipActivationService::class)->activate($microsite, $partner);

        $this->actingAs($partner)->get("/manager/vip-members/{$member->id}/renewal")
            ->assertRedirect(route('manager.vip-members.index'))
            ->assertSessionHas('error');
    }

    public function test_the_add_member_form_offers_the_same_four_package_cards(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $response = $this->actingAs($partner)->get('/manager/vip-members/create');

        $response->assertOk();
        // The same cards as the renewal screen, not a bare dropdown.
        $response->assertDontSee('Select a plan', false);
        foreach (['Growth', 'Professional', 'Growth Plus', 'Premium'] as $name) {
            $response->assertSee($name, false);
        }
        $response->assertSee('product catalogue', false);
        $response->assertSee('validity', false);
        $response->assertSee('4,999', false);

        // One radio per package, so the choice still posts as vip_plan_id.
        $this->assertSame(4, substr_count($response->getContent(), 'name="vip_plan_id"'));
    }

    public function test_creating_a_member_puts_them_on_the_chosen_package(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        $city = $this->makeCity();
        $partner->cities()->attach($city->id);
        $premium = $this->package('premium');

        $this->actingAs($partner)->post('/manager/vip-members', [
            'name' => 'New Member',
            'email' => 'new.member@example.com',
            'password' => 'secret-password-123',
            'vip_plan_id' => $premium->id,
            'business_name' => 'New Member Biz',
            'city_id' => $city->id,
        ])->assertRedirect(route('manager.vip-members.index'));

        $microsite = User::where('email', 'new.member@example.com')->sole()->vipMicrosite;

        $this->assertSame($premium->id, $microsite->vip_plan_id);
        $this->assertSame(100, $microsite->contentQuota('products')['limit']);
        $this->assertSame(100, $microsite->contentQuota('services')['limit']);
    }

    public function test_another_partner_cannot_open_or_approve_someone_elses_renewal(): void
    {
        [, $member, $microsite] = $this->expiredMember();
        $outsider = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($outsider)->get("/manager/vip-members/{$member->id}/renewal")->assertForbidden();
        $this->actingAs($outsider)->patch(
            "/manager/vip-members/{$member->id}/renewal/approve",
            ['vip_plan_id' => $this->package('premium')->id],
        )->assertForbidden();

        $this->assertSame(0, VipRenewal::count());
        $this->assertTrue($microsite->refresh()->hasExpiredPlan());
    }
}
