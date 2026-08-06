<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CartService;
use App\Services\StorefrontContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Covers the AJAX cart flow (add / update / remove returning JSON state) and the
 * StorefrontContext that keeps a single-seller cart branded to its VIP microsite.
 */
class CartAjaxTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function makeProduct(float $price = 500): Product
    {
        return Product::create([
            'name' => 'Wellness Box',
            'slug' => 'wellness-box-'.uniqid(),
            'short_description' => 'x',
            'price' => $price,
            'status' => 'active',
        ]);
    }

    public function test_ajax_add_returns_cart_state(): void
    {
        $product = $this->makeProduct(750);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJson([
                'count' => 2,
                'quantity' => 2,
                'empty' => false,
            ])
            ->assertJsonPath('totals.subtotal', '₹1,500.00')
            ->assertJsonStructure(['count', 'key', 'quantity', 'line_total', 'totals' => ['subtotal', 'shipping', 'total']]);
    }

    public function test_ajax_update_and_remove_report_state(): void
    {
        $product = $this->makeProduct(500);
        $key = app(CartService::class)->keyFor($product->id, null);

        app(CartService::class)->add($product->id, null, 1);

        $this->patchJson(route('cart.update'), ['key' => $key, 'quantity' => 3])
            ->assertOk()
            ->assertJson(['count' => 3, 'quantity' => 3, 'removed' => false]);

        $this->deleteJson(route('cart.remove'), ['key' => $key])
            ->assertOk()
            ->assertJson(['count' => 0, 'removed' => true, 'empty' => true]);
    }

    public function test_ajax_add_rejects_unpriced_product(): void
    {
        $product = Product::create([
            'name' => 'Enquire Only',
            'slug' => 'enquire-only-'.uniqid(),
            'short_description' => 'x',
            'price' => null,
            'status' => 'active',
        ]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id])
            ->assertStatus(422);

        $this->assertSame(0, app(CartService::class)->count());
    }

    public function test_storefront_context_resolves_single_seller_cart(): void
    {
        $this->seedRoles();
        [, $microsite] = $this->makeVipMember(
            $this->makeCommissionPartner($this->makeBranchManager(30), 25),
            $this->makePlan(999),
            $this->makeCity(),
        );
        $product = $this->makeProduct(400);

        app(CartService::class)->add($product->id, $microsite->id, 1);

        $storefront = app(StorefrontContext::class);
        $this->assertTrue($storefront->has());
        $this->assertSame($microsite->id, $storefront->current()->id);
        $this->assertStringContainsString($microsite->business_slug, $storefront->continueUrl());
    }

    public function test_storefront_context_is_absent_for_mixed_sellers(): void
    {
        $this->seedRoles();
        $cp = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [, $storeA] = $this->makeVipMember($cp, $this->makePlan(999), $this->makeCity());
        [, $storeB] = $this->makeVipMember($cp, $this->makePlan(999), $this->makeCity());

        $cart = app(CartService::class);
        $cart->add($this->makeProduct(100)->id, $storeA->id, 1);
        $cart->add($this->makeProduct(200)->id, $storeB->id, 1);

        $storefront = app(StorefrontContext::class);
        $this->assertFalse($storefront->has());
        $this->assertSame(route('products.index'), $storefront->continueUrl());
    }
}
