<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Services\CompanyProductsSwitch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The admin's one switch: hide the company's own products everywhere and lock
 * every package to Catalogue only — reversibly, without losing saved choices.
 */
class CompanyProductsSwitchTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private User $admin;

    private User $owner;

    private VipMicrosite $microsite;

    private VipPlan $plan;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seedRoles();

        $this->admin = User::factory()->create(['status' => 'active']);
        $this->admin->assignRole('admin');

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);
        $this->plan = $this->makePlan(14999);
        $this->plan->update(['selling_mode' => 'direct']);
        [$this->owner, $this->microsite] = $this->makeVipMember($partner, $this->plan, $this->makeCity());

        $this->product = Product::create([
            'name' => 'Company Box', 'slug' => 'box-'.uniqid(), 'short_description' => 'x', 'price' => 500, 'status' => 'active',
        ]);
    }

    private function toggle(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.company-products.toggle'))
            ->assertRedirect();
    }

    public function test_admin_toggles_hiding_company_products_and_selling_buttons(): void
    {
        $this->actingAs($this->admin)->get(route('admin.settings.selling.edit'))
            ->assertOk()->assertSee('Products are shown with price and details')->assertSee('Customers add to cart')
            ->assertDontSee(route('admin.settings.company-products.show'));
        $this->actingAs($this->admin)->get(route('admin.settings.company-products.show'))
            ->assertOk()->assertSee('Hide company products');

        $this->toggle();

        $this->assertTrue(app(CompanyProductsSwitch::class)->hidden());
        // The sidebar link goes.
        $this->actingAs($this->admin)->get(route('admin.business-sales.index'))
            ->assertOk()->assertDontSee('Selling Options by Package');

        // Only the two boxes at the top go; the per-package options stay.
        $this->actingAs($this->admin)->get(route('admin.settings.selling.edit'))
            ->assertOk()
            ->assertDontSee('Products are shown with price and details')->assertDontSee('Customers add to cart')
            ->assertSee('Catalogue only (WhatsApp)')->assertSee('Save selling options');

        $this->toggle();

        $this->assertFalse(app(CompanyProductsSwitch::class)->hidden());
    }

    public function test_hidden_products_leave_shop_cart_and_vip_store_but_packages_are_untouched(): void
    {
        $this->assertTrue($this->plan->fresh()->sellsDirect());
        $this->get(route('products.show', $this->product))->assertOk();

        $this->toggle();

        $this->assertSame(0, Product::onSale()->count());
        $this->get(route('products.show', $this->product))->assertNotFound();
        $this->get(route('products.index'))->assertOk()->assertDontSee('Company Box');
        $this->post(route('cart.add'), ['product_id' => $this->product->id, 'quantity' => 1]);
        $this->assertSame(0, app(\App\Services\CartService::class)->items()->count());
        $this->assertSame(0, $this->microsite->visibleCatalogProducts()->count());

        // Packages keep selling as set, and can still be changed.
        $this->assertTrue($this->plan->fresh()->sellsDirect());
        $this->actingAs($this->admin)
            ->put(route('admin.settings.selling.update'), ['modes' => [$this->plan->id => 'catalogue']])
            ->assertSessionHas('status');
        $this->assertSame('catalogue', $this->plan->fresh()->selling_mode);
        $this->plan->refresh()->update(['selling_mode' => 'direct']);

        // A VIP's saved store choices survive a save attempt while hidden.
        DB::table('vip_products')->insert([
            'vip_microsite_id' => $this->microsite->id, 'product_id' => $this->product->id,
            'is_visible' => true, 'is_featured' => true, 'display_order' => 3,
        ]);
        $this->actingAs($this->owner)->get(route('vip.marketplace.index'))
            ->assertOk()->assertSee('Company products are not on sale right now');
        $this->actingAs($this->owner)->put(route('vip.marketplace.update'), [])->assertSessionHas('error');
        $this->assertSame(1, DB::table('vip_products')->where('vip_microsite_id', $this->microsite->id)->count());

        $this->toggle();

        $this->assertTrue($this->plan->fresh()->sellsDirect());
        $this->get(route('products.show', $this->product))->assertOk();
    }

    public function test_only_admins_can_flip_the_switch(): void
    {
        $this->actingAs($this->owner)
            ->post(route('admin.settings.company-products.toggle'))
            ->assertForbidden();

        $this->assertFalse(app(CompanyProductsSwitch::class)->hidden());
    }
}
