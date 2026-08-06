<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The Super Admin dashboard's "Sales & Revenue" overview must render with live
 * order/commission numbers and its clickable KPI boxes.
 */
class AdminDashboardOverviewTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    public function test_dashboard_shows_sales_and_revenue_overview(): void
    {
        $this->seedRoles();
        $admin = $this->superAdmin();

        // A delivered order → realised company product revenue.
        [, $microsite] = $this->makeVipMember(
            $this->makeCommissionPartner($this->makeBranchManager(30), 25),
            $this->makePlan(999),
            $this->makeCity(),
        );
        $product = Product::create([
            'name' => 'Box', 'slug' => 'box-'.uniqid(),
            'short_description' => 'x', 'price' => 1000, 'status' => 'active',
        ]);
        app(CartService::class)->add($product->id, $microsite->id, 1);
        $customer = User::factory()->create(['status' => 'active']);
        $order = app(OrderService::class)->placeFromCart([
            'customer_name' => 'B', 'customer_phone' => '9998887776',
            'address' => 'x', 'city' => 'x', 'state' => 'x', 'pincode' => '284001',
            'payment_method' => 'cod', 'payment_outcome' => 'success',
        ], $customer);
        app(OrderService::class)->markDelivered($order->fresh());

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Sales &amp; Revenue', false)
            ->assertSee('Orders Today')
            ->assertSee('Pending Orders')
            ->assertSee('Company Commission')
            ->assertSee('Revenue from VIP Plans')
            ->assertSee('Revenue from Products')
            // KPI boxes link to their related pages.
            ->assertSee(route('admin.orders.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.revenue.index'), false);
    }
}
