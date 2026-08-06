<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The admin new-order sound alert: a poll endpoint that reports newly-arrived
 * orders, and the alert partial that only super_admin/admin see.
 */
class AdminOrderAlertTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function admin(string $role = 'super_admin'): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'order_number' => 'GL'.uniqid(),
            'customer_name' => 'Jane', 'customer_email' => 'jane@example.com', 'customer_phone' => '999',
            'address' => 'x', 'city' => 'x', 'state' => 'x', 'pincode' => '000000',
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'confirmed',
            'subtotal' => 100, 'shipping' => 0, 'total' => 100, 'placed_at' => now(),
        ]);
    }

    public function test_poll_reports_new_orders_since_given_id(): void
    {
        $this->seedRoles();
        $first = $this->makeOrder();

        // No orders newer than the latest → nothing new.
        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.poll', ['since' => $first->id]))
            ->assertOk()
            ->assertJson(['latest_id' => $first->id, 'new' => 0]);

        $second = $this->makeOrder();

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.poll', ['since' => $first->id]))
            ->assertOk()
            ->assertJson(['latest_id' => $second->id, 'new' => 1]);
    }

    public function test_alert_partial_renders_for_admin(): void
    {
        $this->seedRoles();

        $this->actingAs($this->admin('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-order-alert', false)
            ->assertSee(route('admin.orders.poll'), false);
    }

    public function test_alert_partial_hidden_from_sub_admin(): void
    {
        $this->seedRoles();

        $this->actingAs($this->admin('sub_admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('data-order-alert', false);
    }
}
