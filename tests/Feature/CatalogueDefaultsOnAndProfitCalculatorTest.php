<?php

namespace Tests\Feature;

use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The company catalogue sells on every VIP page by default.
 *
 * A member used to have to tick each product on before it appeared, so new
 * members had an empty shop and a product added to the catalogue reached nobody
 * until every member opted in one at a time. Now the absence of a `vip_products`
 * row means visible, and only an explicit row with `is_visible = 0` hides one.
 *
 * The same page carries a profit calculator, fed from the real commission rules.
 */
class CatalogueDefaultsOnAndProfitCalculatorTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function plan(): VipPlan
    {
        $plan = $this->makePlan(1000);
        $plan->update(['product_limit' => 15, 'service_limit' => 15]);

        return $plan;
    }

    /**
     * @return array{0: User, 1: VipMicrosite}
     */
    private function liveMember(): array
    {
        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [$member, $microsite] = $this->makeVipMember($partner, $this->plan(), $this->makeCity());
        app(VipActivationService::class)->activate($microsite, $partner);

        return [$member, $microsite->refresh()];
    }

    private function product(string $name, float $price, ?float $mrp = null): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'short_description' => 'x',
            'price' => $price,
            'mrp' => $mrp,
            'status' => 'active',
        ]);
    }

    private function vipCommissionRule(float $percent): CommissionRule
    {
        return CommissionRule::create([
            'scope' => 'global',
            'scope_id' => 0,
            'role' => 'vip_member',
            'type' => 'percent',
            'value' => $percent,
            'status' => 'active',
        ]);
    }

    public function test_a_product_shows_on_a_page_that_has_never_been_configured(): void
    {
        [, $microsite] = $this->liveMember();
        $product = $this->product('Immunity Booster', 499);

        // Nothing in the pivot at all.
        $this->assertSame(0, DB::table('vip_products')->count());

        $this->assertTrue($microsite->showsCatalogProduct($product));
        $this->assertTrue($microsite->visibleCatalogProducts()->contains('id', $product->id));
    }

    public function test_a_product_added_to_the_catalogue_later_reaches_every_page(): void
    {
        [$member, $microsite] = $this->liveMember();
        $this->product('Immunity Booster', 499);

        // The member saves their store, which writes rows for what exists now.
        $this->actingAs($member)->put('/vip/marketplace')->assertRedirect();

        // Super Admin then adds something new.
        $newcomer = $this->product('Protein Powder', 1299);

        $this->assertTrue($microsite->showsCatalogProduct($newcomer));
        $this->assertTrue($microsite->visibleCatalogProducts()->contains('id', $newcomer->id));
    }

    public function test_a_member_can_switch_one_off(): void
    {
        [$member, $microsite] = $this->liveMember();
        $keep = $this->product('Immunity Booster', 499);
        $hide = $this->product('Protein Powder', 1299);

        $this->actingAs($member)->put('/vip/marketplace', [
            'products' => [
                $keep->id => ['show' => 1],
                // $hide simply not ticked.
            ],
        ])->assertRedirect(route('vip.marketplace.index'));

        $this->assertTrue($microsite->showsCatalogProduct($keep));
        $this->assertFalse($microsite->showsCatalogProduct($hide));

        $visible = $microsite->visibleCatalogProducts();
        $this->assertTrue($visible->contains('id', $keep->id));
        $this->assertFalse($visible->contains('id', $hide->id));
    }

    public function test_switching_one_off_does_not_hide_it_from_other_members(): void
    {
        [$member, $microsite] = $this->liveMember();
        [, $other] = $this->liveMember();
        $product = $this->product('Protein Powder', 1299);

        $this->actingAs($member)->put('/vip/marketplace', ['products' => []])->assertRedirect();

        $this->assertFalse($microsite->showsCatalogProduct($product));
        $this->assertTrue($other->showsCatalogProduct($product), 'visibility is per member');
    }

    public function test_an_inactive_product_is_never_shown(): void
    {
        [, $microsite] = $this->liveMember();
        $product = $this->product('Discontinued Thing', 499);
        $product->update(['status' => 'inactive']);

        $this->assertFalse($microsite->visibleCatalogProducts()->contains('id', $product->id));
    }

    public function test_featured_products_come_first_then_display_order(): void
    {
        [$member, $microsite] = $this->liveMember();
        $plain = $this->product('Aaa Plain', 100);
        $featured = $this->product('Zzz Featured', 100);

        $this->actingAs($member)->put('/vip/marketplace', [
            'products' => [
                $plain->id => ['show' => 1, 'order' => 1],
                $featured->id => ['show' => 1, 'featured' => 1, 'order' => 9],
            ],
        ])->assertRedirect();

        $this->assertSame(
            [$featured->id, $plain->id],
            $microsite->visibleCatalogProducts()->pluck('id')->all(),
        );
    }

    public function test_the_store_page_ticks_everything_on_by_default(): void
    {
        [$member] = $this->liveMember();
        $this->product('Immunity Booster', 499);

        $response = $this->actingAs($member)->get('/vip/marketplace');

        $response->assertOk();
        // The Show checkbox is checked although no pivot row exists.
        $response->assertSee('checked', false);
        $response->assertSee('already on sale on your page', false);
    }

    public function test_the_calculator_shows_what_a_sale_actually_pays(): void
    {
        [$member] = $this->liveMember();
        $this->product('Immunity Booster', 499);
        $this->vipCommissionRule(15);

        $response = $this->actingAs($member)->get('/vip/marketplace');

        $response->assertOk();
        $response->assertSee('Profit Calculator', false);
        // 15% of 499 = 74.85, shown both in the row and in the calculator payload.
        $response->assertSee('74.85', false);
        $response->assertSee('data-profit-calc', false);
    }

    public function test_the_calculator_uses_the_most_specific_rule(): void
    {
        [$member] = $this->liveMember();
        $product = $this->product('Immunity Booster', 499);
        $this->vipCommissionRule(15);

        // A product-scope rule beats the global one.
        CommissionRule::create([
            'scope' => 'product',
            'scope_id' => $product->id,
            'role' => 'vip_member',
            'type' => 'percent',
            'value' => 25,
            'status' => 'active',
        ]);

        $response = $this->actingAs($member)->get('/vip/marketplace');

        $response->assertOk();
        // 25% of 499 = 124.75, not 15%'s 74.85.
        $response->assertSee('124.75', false);
        $response->assertDontSee('74.85', false);
    }

    public function test_the_calculator_is_hidden_when_no_commission_is_configured(): void
    {
        [$member] = $this->liveMember();
        $this->product('Immunity Booster', 499);

        $response = $this->actingAs($member)->get('/vip/marketplace');

        $response->assertOk();
        $response->assertDontSee('Profit Calculator', false);
    }

    public function test_the_public_storefront_lists_the_catalogue_without_any_opt_in(): void
    {
        [, $microsite] = $this->liveMember();
        $product = $this->product('Immunity Booster', 499);

        $response = $this->get($microsite->publicPath());

        $response->assertOk();
        $response->assertSee($product->name, false);
    }
}
