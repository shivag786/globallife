<?php

namespace Tests\Feature;

use App\Models\BusinessProduct;
use App\Models\BusinessService;
use App\Models\VipMicrosite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A VIP business page's own products and services each have a full detail
 * page, and its products can be saved to the wishlist.
 */
class MicrositeDetailPagesTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private VipMicrosite $microsite;

    private BusinessProduct $product;

    private BusinessService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seedRoles();

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);
        $plan = $this->makePlan(14999);
        $plan->update(['product_limit' => 10, 'service_limit' => 10, 'selling_mode' => 'catalogue']);

        [, $this->microsite] = $this->makeVipMember($partner, $plan, $this->makeCity());
        $this->microsite->update([
            'activated_at' => now(), 'plan_expires_at' => now()->addYear(), 'whatsapp_number' => '98765 43210',
        ]);

        $this->product = BusinessProduct::create([
            'vip_microsite_id' => $this->microsite->id, 'name' => 'Herbal Tea', 'slug' => 'herbal-tea',
            'short_description' => 'A calming blend.', 'long_description' => 'Steep for five minutes.',
            'mrp' => 500, 'show_pricing' => true, 'status' => 'published', 'brand' => 'LeafCo',
        ]);
        $this->service = BusinessService::create([
            'vip_microsite_id' => $this->microsite->id, 'name' => 'Full Body Massage', 'slug' => 'full-body-massage',
            'long_description' => 'Sixty minutes of relaxation.', 'mrp' => 1200, 'show_pricing' => true,
            'status' => 'published', 'show_book_now' => true,
        ]);
    }

    public function test_product_detail_page_renders(): void
    {
        $path = $this->microsite->productPath($this->product);
        $this->assertStringEndsWith('/product/'.$this->product->id.'-herbal-tea', $path);

        $this->get($path)->assertOk()
            ->assertSee('Herbal Tea')
            ->assertSee('Steep for five minutes.')
            ->assertSee('LeafCo')
            ->assertSee('Order on WhatsApp');
    }

    public function test_service_detail_page_renders(): void
    {
        $this->get($this->microsite->servicePath($this->service))->assertOk()
            ->assertSee('Full Body Massage')
            ->assertSee('Sixty minutes of relaxation.')
            ->assertSee('Book Now');
    }

    public function test_main_page_links_to_the_detail_pages(): void
    {
        $this->get($this->microsite->publicPath())->assertOk()
            ->assertSee($this->microsite->productPath($this->product), false)
            ->assertSee($this->microsite->servicePath($this->service), false);
    }

    public function test_unpublished_or_foreign_items_are_not_found(): void
    {
        $this->product->update(['status' => 'draft']);
        $this->get($this->microsite->productPath($this->product))->assertNotFound();

        // An id that belongs to another business is not served under this page.
        $this->get($this->microsite->publicPath().'/service/999999')->assertNotFound();
    }

    public function test_business_product_can_be_saved_to_the_wishlist(): void
    {
        $this->post(route('wishlist.add'), ['business_product_id' => $this->product->id])->assertRedirect();

        $this->get(route('wishlist.index'))->assertOk()
            ->assertSee('Herbal Tea')
            ->assertSee($this->microsite->business_name);

        $this->delete(route('wishlist.remove'), ['business_product_id' => $this->product->id])->assertRedirect();
        $this->get(route('wishlist.index'))->assertOk()->assertSee('Your wishlist is empty.');
    }
}
