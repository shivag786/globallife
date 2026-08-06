<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Delivery promise + order tracking: the global delivery-days setting drives the
 * product-page estimate and each order's default expected delivery date; admins
 * override the date; and status changes stamp the tracking milestones.
 */
class DeliveryTrackingTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function makeProduct(float $price = 500): Product
    {
        return Product::create([
            'name' => 'Wellness Box', 'slug' => 'wellness-'.uniqid(),
            'short_description' => 'x', 'price' => $price, 'status' => 'active',
        ]);
    }

    private function placeOrder(?User $customer = null): Order
    {
        app(CartService::class)->add($this->makeProduct()->id, null, 1);

        return app(OrderService::class)->placeFromCart([
            'customer_name' => 'Jane', 'customer_phone' => '9999999999',
            'address' => 'x', 'city' => 'x', 'state' => 'x', 'pincode' => '000000',
            'payment_method' => 'cod', 'payment_outcome' => 'success',
        ], $customer ?? User::factory()->create(['status' => 'active']));
    }

    private function admin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_default_delivery_days_is_five(): void
    {
        $this->assertSame(5, app(DeliveryService::class)->days());
    }

    public function test_order_gets_default_expected_delivery_from_setting(): void
    {
        $this->seedRoles();
        app(SettingsService::class)->set('delivery_days', '2');

        $order = $this->placeOrder();

        $this->assertNotNull($order->expected_delivery_date);
        $this->assertSame(
            Carbon::now()->addDays(2)->toDateString(),
            $order->expected_delivery_date->toDateString(),
        );
    }

    public function test_status_changes_stamp_tracking_milestones(): void
    {
        $order = Order::create([
            'order_number' => 'GL'.uniqid(), 'customer_name' => 'A', 'customer_email' => 'a@x.com',
            'customer_phone' => '9', 'address' => 'x', 'city' => 'x', 'state' => 'x', 'pincode' => '000000',
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
            'subtotal' => 100, 'shipping' => 0, 'total' => 100, 'placed_at' => now(),
        ]);

        $orders = app(OrderService::class);

        $orders->updateStatus($order, 'processing');
        $this->assertNotNull($order->fresh()->processing_at);

        $orders->updateStatus($order, 'dispatched');
        $this->assertNotNull($order->fresh()->dispatched_at);

        $orders->updateStatus($order, 'delivered');
        $fresh = $order->fresh();
        $this->assertSame('delivered', $fresh->status);
        $this->assertNotNull($fresh->delivered_at);
    }

    public function test_admin_can_override_expected_delivery_date(): void
    {
        $this->seedRoles();
        $order = $this->placeOrder();
        $newDate = Carbon::now()->addDays(9)->toDateString();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.update-delivery', $order), ['expected_delivery_date' => $newDate])
            ->assertRedirect();

        $this->assertSame($newDate, $order->fresh()->expected_delivery_date->toDateString());
    }

    public function test_admin_order_page_renders_tracker_and_delivery_editor(): void
    {
        $this->seedRoles();
        $order = $this->placeOrder();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Expected Delivery Date')
            ->assertSee('Order Confirmed')
            ->assertSee('Arriving by');
    }

    public function test_product_page_shows_delivery_promise(): void
    {
        $product = $this->makeProduct();
        $expected = Carbon::now('Asia/Kolkata')->addDays(5)->format('D, d M Y');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('delivery by')
            ->assertSee($expected);
    }

    public function test_customer_order_page_shows_tracker(): void
    {
        $this->seedRoles();
        $customer = User::factory()->create(['status' => 'active']);
        $order = $this->placeOrder($customer);
        $order->update(['status' => 'dispatched', 'dispatched_at' => now()]);

        $this->actingAs($customer)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Arriving by')
            ->assertSee('Shipped')
            ->assertSee('Order Confirmed');
    }
}
