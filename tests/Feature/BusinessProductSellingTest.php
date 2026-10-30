<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\BusinessProduct;
use App\Models\CommissionEarning;
use App\Models\Order;
use App\Models\PendingOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Services\CartService;
use App\Services\RazorpayPaymentConfirmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * A business selling its OWN products: catalogue (WhatsApp) vs direct sell by
 * package, the one-shop cart, the business delivering, and the company paying
 * over what its Razorpay collected — with proof, exactly once.
 */
class BusinessProductSellingTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private VipPlan $plan;

    private User $owner;

    private VipMicrosite $microsite;

    private BusinessProduct $product;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seedRoles();

        $branch = $this->makeBranchManager(30);
        $partner = $this->makeCommissionPartner($branch, 20);
        $this->plan = $this->makePlan(14999);
        $this->plan->update(['product_limit' => 10, 'service_limit' => 10, 'selling_mode' => 'direct']);

        [$this->owner, $this->microsite] = $this->makeVipMember($partner, $this->plan, $this->makeCity());
        $this->microsite->update([
            'activated_at' => now(), 'plan_expires_at' => now()->addYear(), 'whatsapp_number' => '98765 43210',
        ]);

        $this->product = $this->makeProduct($this->microsite, ['name' => 'BP Monitor', 'mrp' => 2000, 'offer_price' => 1500, 'stock' => 5]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeProduct(VipMicrosite $microsite, array $overrides = []): BusinessProduct
    {
        $name = $overrides['name'] ?? 'Product '.uniqid();

        return BusinessProduct::create($overrides + [
            'vip_microsite_id' => $microsite->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'mrp' => 500,
            'offer_price' => null,
            'show_pricing' => true,
            'status' => 'published',
        ]);
    }

    private function customer(): User
    {
        Role::findOrCreate('customer', 'web');
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('customer');

        return $user;
    }

    private function address(User $user): Address
    {
        return $user->addresses()->create([
            'name' => 'Jane', 'phone' => '9999999999', 'address' => '12 Test St',
            'city' => 'Jhansi', 'state' => 'UP', 'pincode' => '284001', 'is_default' => true,
        ]);
    }

    private function placeCodOrder(int $quantity = 2): Order
    {
        $customer = $this->customer();
        $address = $this->address($customer);

        $this->actingAs($customer)
            ->postJson(route('cart.add'), ['business_product_id' => $this->product->id, 'quantity' => $quantity])
            ->assertOk();

        $this->post(route('checkout.store'), ['address_id' => $address->id, 'payment_choice' => 'cod'])
            ->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    private function deliveredOnlineOrder(float $total): Order
    {
        $order = Order::create([
            'order_number' => 'GLT'.uniqid(),
            'user_id' => $this->customer()->id,
            'fulfilled_by_microsite_id' => $this->microsite->id,
            'customer_name' => 'Jane', 'customer_email' => 'j@example.com', 'customer_phone' => '9999999999',
            'address' => 'x', 'city' => 'Jhansi', 'state' => 'UP', 'pincode' => '284001',
            'payment_method' => 'online', 'payment_gateway' => 'razorpay', 'payment_status' => 'paid',
            'status' => 'delivered', 'subtotal' => $total, 'shipping' => 0, 'total' => $total,
            'placed_at' => now(), 'delivered_at' => now(),
        ]);

        return $order;
    }

    // ---------------------------------------------------------------- modes

    public function test_catalogue_package_shows_whatsapp_order_and_refuses_the_cart(): void
    {
        $this->plan->update(['selling_mode' => 'catalogue']);

        $page = $this->get($this->microsite->publicPath())->assertOk();
        $page->assertSee('Order on WhatsApp');
        $page->assertSee('https://wa.me/919876543210?text=', false);
        $page->assertDontSee('Add to cart');

        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id])
            ->assertStatus(422);
        $this->assertTrue(app(CartService::class)->isEmpty());
    }

    public function test_direct_package_adds_the_business_product_at_its_database_price(): void
    {
        $this->get($this->microsite->publicPath())->assertOk()->assertSee('Add to cart')->assertDontSee('Order on WhatsApp');

        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonPath('quantity', 2);

        $item = app(CartService::class)->items()->first();
        $this->assertSame('business', $item['type']);
        $this->assertSame(1500.0, $item['unit_price']);
        $this->assertSame(3000.0, $item['line_total']);
        $this->assertSame($this->microsite->id, $item['seller_id']);

        // Repricing after it was added is what the customer pays.
        $this->product->update(['offer_price' => 1400]);
        $this->assertSame(2800.0, app(CartService::class)->totals()['total']);
    }

    public function test_switching_the_package_to_catalogue_empties_business_lines_from_the_cart(): void
    {
        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id])->assertOk();

        $this->plan->update(['selling_mode' => 'catalogue']);

        $this->assertTrue(app(CartService::class)->items()->isEmpty());
    }

    public function test_out_of_stock_unpublished_and_unpriced_products_cannot_be_bought(): void
    {
        foreach ([['stock' => 0], ['status' => 'draft'], ['mrp' => null, 'offer_price' => null], ['show_pricing' => false]] as $state) {
            $product = $this->makeProduct($this->microsite, $state);
            $this->postJson(route('cart.add'), ['business_product_id' => $product->id])->assertStatus(422);
        }

        // Quantity is clamped to the stock left.
        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id, 'quantity' => 9])->assertOk();
        $this->assertSame(5, app(CartService::class)->items()->first()['quantity']);
    }

    public function test_an_expired_page_stops_selling(): void
    {
        $this->microsite->update(['plan_expires_at' => now()->subDay()]);

        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id])->assertStatus(422);
    }

    // ------------------------------------------------------------ one shop

    public function test_a_business_product_cannot_join_a_cart_from_another_shop_without_consent(): void
    {
        $catalog = Product::create(['name' => 'Company Box', 'slug' => 'box-'.uniqid(), 'short_description' => 'x', 'price' => 500, 'status' => 'active']);
        $this->postJson(route('cart.add'), ['product_id' => $catalog->id])->assertOk();

        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id])
            ->assertStatus(409)
            ->assertJsonPath('conflict', true);
        $this->assertSame(1, app(CartService::class)->count());

        // Agreeing to start a new cart replaces it.
        $this->postJson(route('cart.add'), ['business_product_id' => $this->product->id, 'replace_cart' => 1])->assertOk();
        $items = app(CartService::class)->items();
        $this->assertCount(1, $items);
        $this->assertSame('business', $items->first()['type']);

        // ...and the reverse direction is refused too.
        $this->postJson(route('cart.add'), ['product_id' => $catalog->id])->assertStatus(409);

        // A second business is another shop.
        [, $other] = $this->makeVipMember($this->makeCommissionPartner($this->makeBranchManager(30), 20), $this->plan, $this->makeCity());
        $other->update(['activated_at' => now(), 'plan_expires_at' => now()->addYear()]);
        $otherProduct = $this->makeProduct($other);
        $this->postJson(route('cart.add'), ['business_product_id' => $otherProduct->id])->assertStatus(409);
    }

    // -------------------------------------------------------------- orders

    public function test_cod_checkout_creates_a_business_fulfilled_order_with_no_commission(): void
    {
        $order = $this->placeCodOrder(2);

        $this->assertSame($this->microsite->id, $order->fulfilled_by_microsite_id);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('3000.00', (string) $order->total);

        $item = $order->items()->first();
        $this->assertNull($item->product_id);
        $this->assertSame($this->product->id, $item->business_product_id);
        $this->assertSame('BP Monitor', $item->product_name);

        $this->assertSame(0, CommissionEarning::count());
        $this->assertSame(3, $this->product->fresh()->stock);
    }

    public function test_a_paid_razorpay_snapshot_keeps_the_business_line(): void
    {
        $customer = $this->customer();

        $pending = PendingOrder::create([
            'razorpay_order_id' => 'order_BIZ1',
            'user_id' => $customer->id,
            'customer_name' => 'Jane', 'customer_phone' => '9999999999',
            'address' => '12 Test St', 'city' => 'Jhansi', 'state' => 'UP', 'pincode' => '284001',
            'items' => [[
                'product_id' => null,
                'business_product_id' => $this->product->id,
                'product_name' => 'BP Monitor',
                'product_sku' => null,
                'seller_id' => $this->microsite->id,
                'unit_price' => 1500.0,
                'quantity' => 1,
                'line_total' => 1500.0,
            ]],
            'subtotal' => 1500, 'shipping' => 0, 'total' => 1500, 'currency' => 'INR', 'status' => 'pending',
        ]);

        $order = app(RazorpayPaymentConfirmer::class)->confirm($pending, 'pay_X1', 'sig');

        $this->assertSame($this->microsite->id, $order->fulfilled_by_microsite_id);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame($this->product->id, $order->items->first()->business_product_id);
        $this->assertSame(4, $this->product->fresh()->stock);
    }

    public function test_the_business_moves_its_own_order_forward_to_delivered(): void
    {
        $order = $this->placeCodOrder();

        $this->actingAs($this->owner)->get(route('vip.orders.index'))->assertOk()->assertSee($order->order_number);
        $this->get(route('vip.orders.show', $order))->assertOk()->assertSee('Collect');

        $this->patch(route('vip.orders.update-status', $order), ['status' => 'dispatched'])->assertRedirect();
        $this->assertSame('dispatched', $order->fresh()->status);

        // No going back.
        $this->patch(route('vip.orders.update-status', $order), ['status' => 'processing']);
        $this->assertSame('dispatched', $order->fresh()->status);

        // Cancelling is the company's call.
        $this->patch(route('vip.orders.update-status', $order), ['status' => 'cancelled'])->assertSessionHasErrors('status');

        $this->patch(route('vip.orders.update-status', $order), ['status' => 'delivered']);
        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->delivered_at);
    }

    public function test_another_business_cannot_see_or_update_the_order(): void
    {
        $order = $this->placeCodOrder();

        [$stranger] = $this->makeVipMember($this->makeCommissionPartner($this->makeBranchManager(30), 20), $this->plan, $this->makeCity());

        $this->actingAs($stranger)->get(route('vip.orders.show', $order))->assertNotFound();
        $this->patch(route('vip.orders.update-status', $order), ['status' => 'delivered'])->assertNotFound();
        $this->get(route('vip.orders.index'))->assertOk()->assertDontSee($order->order_number);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    // ---------------------------------------------------------- settlement

    public function test_only_delivered_online_orders_are_payable_and_settle_once_with_proof(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $this->placeCodOrder();                     // COD: business collects it
        $this->deliveredOnlineOrder(1200);
        $this->deliveredOnlineOrder(800);
        $open = $this->deliveredOnlineOrder(500);
        $open->update(['status' => 'dispatched']);  // not delivered yet

        $this->actingAs($admin)->get(route('admin.business-sales.index'))
            ->assertOk()
            ->assertSee($this->microsite->business_name)
            ->assertSee('₹2,000.00');

        // Proof is required.
        $this->post(route('admin.business-sales.settle', $this->microsite), [])
            ->assertSessionHasErrors(['utr_number', 'payment_screenshot']);

        $this->post(route('admin.business-sales.settle', $this->microsite), [
            'payment_screenshot' => UploadedFile::fake()->image('neft.png'),
            'note' => 'NEFT to HDFC',
        ])->assertSessionHas('status');

        $settlement = $this->microsite->settlements()->sole();
        $this->assertSame('2000.00', (string) $settlement->amount);
        $this->assertSame(2, $settlement->orders_count);
        Storage::disk('public')->assertExists($settlement->payment_screenshot_path);

        // Nothing left to pay, so a second transfer is refused.
        $this->post(route('admin.business-sales.settle', $this->microsite), ['utr_number' => 'UTR2'])
            ->assertSessionHas('error');
        $this->assertSame(1, $this->microsite->settlements()->count());

        // The late delivery becomes payable on its own.
        $open->update(['status' => 'delivered']);
        $this->post(route('admin.business-sales.settle', $this->microsite), ['utr_number' => 'UTR3'])->assertSessionHas('status');
        $this->assertSame('500.00', (string) $this->microsite->settlements()->latest('id')->first()->amount);

        // The business sees the transfer and its proof.
        $this->actingAs($this->owner)->get(route('vip.orders.index'))->assertOk()->assertSee('UTR3')->assertSee('₹2,500.00');
    }

    public function test_only_super_admin_and_admin_reach_the_reports_and_selling_options(): void
    {
        $subAdmin = $this->makeSubAdmin(['products']);

        $this->actingAs($subAdmin)->get(route('admin.business-sales.index'))->assertForbidden();
        $this->get(route('admin.settings.selling.edit'))->assertForbidden();

        $this->actingAs($this->owner)->get(route('admin.business-sales.index'))->assertForbidden();
        $this->post(route('admin.business-sales.settle', $this->microsite), ['utr_number' => 'X'])->assertForbidden();
    }

    public function test_admin_switches_a_package_between_catalogue_and_direct(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('admin.settings.selling.edit'))->assertOk()->assertSee($this->plan->name);

        $this->put(route('admin.settings.selling.update'), ['modes' => [$this->plan->id => 'catalogue']])->assertRedirect();
        $this->assertSame('catalogue', $this->plan->fresh()->selling_mode);
        $this->assertFalse($this->microsite->fresh()->sellsDirect());

        $this->put(route('admin.settings.selling.update'), ['modes' => [$this->plan->id => 'anything']])
            ->assertSessionHasErrors();
    }
}
