<?php

namespace Tests\Feature;

use App\Models\BusinessService;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A service is priced with two figures — MRP and sale price — and everything a
 * customer sees is derived from them.
 *
 * The discount percentage and the struck-through price used to be typed in by
 * hand alongside, which let a card advertise "50% OFF" over prices that said
 * nothing of the kind. Now they are computed, so the badge, the strike-through
 * and the price charged cannot disagree.
 */
class ServiceOfferPricingTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    /**
     * A plan with room for services — makePlan() leaves the caps at zero, which
     * the quota guard reads as "no services allowed".
     */
    private function plan(): VipPlan
    {
        $plan = $this->makePlan(1000);
        $plan->update(['product_limit' => 15, 'service_limit' => 15]);

        return $plan;
    }

    private function microsite(): VipMicrosite
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->plan(), $this->makeCity());

        Auth::login($member);

        return $microsite;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Full Body Checkup',
            'status' => 'published',
        ], $overrides);
    }

    /**
     * The three cases, exactly as specified: MRP alone shows the MRP, sale price
     * alone shows the sale price, and both show the sale price with the MRP
     * struck through and the saving worked out.
     *
     * @return array<string, array{0: ?float, 1: ?float, 2: float, 3: ?float, 4: ?int}>
     */
    public static function pricingCases(): array
    {
        return [
            // mrp, sale,  shown, struck, discount
            'MRP only' => [499.0, null, 499.0, null, null],
            'sale only' => [null, 410.0, 410.0, null, null],
            'both' => [499.0, 410.0, 410.0, 499.0, 18],
            'equal is no discount' => [499.0, 499.0, 499.0, null, null],
        ];
    }

    #[DataProvider('pricingCases')]
    public function test_the_derived_price_matches_the_specified_cases(
        ?float $mrp,
        ?float $sale,
        float $shown,
        ?float $struck,
        ?int $discount,
    ): void {
        $service = new BusinessService(['mrp' => $mrp, 'offer_price' => $sale]);

        $this->assertSame($shown, $service->sellingPrice());
        $this->assertSame($struck, $service->strikeThroughPrice());
        $this->assertSame($discount, $service->discountPercentage());
    }

    public function test_a_service_with_no_price_at_all_shows_nothing(): void
    {
        $service = new BusinessService(['mrp' => null, 'offer_price' => null]);

        $this->assertFalse($service->hasPrice());
        $this->assertNull($service->sellingPrice());
        $this->assertNull($service->discountPercentage());
    }

    public function test_the_discount_is_computed_on_save_not_taken_from_the_form(): void
    {
        $microsite = $this->microsite();

        // A form claiming a 90% saving and a made-up strike price is ignored:
        // both are derived from the two real figures.
        $this->post('/vip/services', $this->payload([
            'mrp' => 499,
            'offer_price' => 410,
            'discount_percent' => 90,
            'strike_price' => 9999,
        ]))->assertRedirect(route('vip.services.index'));

        $service = $microsite->services()->sole();

        // 89 off 499 is 17.84%, which the public badge rounds to 18%.
        $this->assertEquals(17.84, round((float) $service->discount_percent, 2));
        $this->assertNull($service->strike_price);
        $this->assertSame(18, $service->discountPercentage());
    }

    public function test_a_sale_price_above_the_mrp_is_refused(): void
    {
        $microsite = $this->microsite();

        $this->post('/vip/services', $this->payload(['mrp' => 400, 'offer_price' => 500]))
            ->assertSessionHasErrors('offer_price');

        $this->assertSame(0, $microsite->services()->count());
    }

    public function test_a_sale_price_on_its_own_is_allowed(): void
    {
        $microsite = $this->microsite();

        $this->post('/vip/services', $this->payload(['offer_price' => 410]))
            ->assertRedirect(route('vip.services.index'));

        $service = $microsite->services()->sole();

        $this->assertSame(410.0, $service->sellingPrice());
        $this->assertNull($service->discount_percent);
    }

    public function test_the_slug_is_generated_from_the_name_and_shown_read_only(): void
    {
        $microsite = $this->microsite();

        $this->post('/vip/services', $this->payload(['name' => 'Full Body Checkup & Scan']))
            ->assertRedirect();

        $service = $microsite->services()->sole();

        $this->assertSame('full-body-checkup-scan', $service->slug);
        $this->assertSame('Full Body Checkup & Scan', $service->meta_title);

        // The field is on the form for visibility only — disabled, so a browser
        // never posts it, and a forged one cannot override the generated slug.
        $form = $this->get('/vip/services/create');
        $form->assertOk();
        $form->assertSee('data-slug-preview', false);
        $form->assertSee('disabled', false);
    }

    public function test_a_forged_slug_cannot_override_the_generated_one(): void
    {
        $microsite = $this->microsite();

        $this->post('/vip/services', $this->payload([
            'name' => 'Dental Cleaning',
            'slug' => 'something-else-entirely',
        ]))->assertRedirect();

        $this->assertSame('dental-cleaning', $microsite->services()->sole()->slug);
    }

    public function test_the_form_no_longer_asks_for_a_discount_or_strike_price(): void
    {
        $this->microsite();

        $response = $this->get('/vip/services/create');

        $response->assertOk();
        $response->assertSee('name="mrp"', false);
        $response->assertSee('name="offer_price"', false);
        $response->assertDontSee('name="discount_percent"', false);
        $response->assertDontSee('name="strike_price"', false);
    }

    public function test_the_public_card_strikes_the_mrp_and_shows_the_saving(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [, $microsite] = $this->makeVipMember($partner, $this->plan(), $this->makeCity());
        app(\App\Services\VipActivationService::class)->activate($microsite, $partner);

        $microsite->services()->create([
            'name' => 'Full Body Checkup',
            'slug' => 'full-body-checkup',
            'mrp' => 499,
            'offer_price' => 410,
            'show_pricing' => true,
            'status' => 'published',
        ]);

        $response = $this->get($microsite->publicPath());

        $response->assertOk();
        $response->assertSee('18% OFF', false);
        $response->assertSee('410.00', false);
        $response->assertSee('499.00', false);
        $response->assertSee('line-through', false);
    }

    public function test_the_public_card_shows_one_price_when_only_the_mrp_is_set(): void
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [, $microsite] = $this->makeVipMember($partner, $this->plan(), $this->makeCity());
        app(\App\Services\VipActivationService::class)->activate($microsite, $partner);

        $microsite->services()->create([
            'name' => 'Consultation',
            'slug' => 'consultation',
            'mrp' => 499,
            'show_pricing' => true,
            'status' => 'published',
        ]);

        $response = $this->get($microsite->publicPath());

        $response->assertOk();
        $response->assertSee('499.00', false);
        $response->assertDontSee('% OFF', false);
        $response->assertDontSee('line-through', false);
    }
}
