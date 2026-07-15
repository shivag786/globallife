<?php

namespace Tests\Feature;

use App\Models\CommissionEarning;
use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

class OrderCommissionTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function rule(string $role, float $value): void
    {
        CommissionRule::create([
            'scope' => 'global', 'scope_id' => 0, 'role' => $role,
            'type' => 'percent', 'value' => $value, 'status' => 'active',
        ]);
    }

    private function checkoutData(string $method = 'cod'): array
    {
        return [
            'customer_name' => 'John Buyer',
            'customer_email' => 'john.buyer@example.com',
            'customer_phone' => '9998887776',
            'address' => '12 Test Street',
            'city' => 'Jhansi',
            'state' => 'UP',
            'pincode' => '284001',
            'payment_method' => $method,
            'payment_outcome' => 'success',
        ];
    }

    public function test_order_records_pending_earnings_then_delivery_approves_and_credits_wallets(): void
    {
        $this->seedRoles();
        $bm = $this->makeBranchManager(30);
        $cp = $this->makeCommissionPartner($bm, 25);
        [$vip, $microsite] = $this->makeVipMember($cp, $this->makePlan(999), $this->makeCity());

        $product = Product::create([
            'name' => 'Protein Box', 'slug' => 'protein-box-'.uniqid(),
            'short_description' => 'x', 'price' => 1000, 'status' => 'active',
        ]);

        $this->rule('vip_member', 10);
        $this->rule('commission_partner', 7);
        $this->rule('branch_manager', 5);

        app(CartService::class)->add($product->id, $microsite->id, 2); // 2 × ₹1000 = ₹2000 line

        $orders = app(OrderService::class);
        $order = $orders->placeFromCart($this->checkoutData(), null);

        $this->assertNotNull($order);

        // A customer account was auto-created with the customer role.
        $customer = User::where('email', 'john.buyer@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertTrue($customer->hasRole('customer'));

        // Earnings recorded as PENDING on a ₹2000 base — nothing in wallets yet.
        $this->assertSame(3, CommissionEarning::where('order_id', $order->id)->count());
        $this->assertEqualsWithDelta(200, (float) CommissionEarning::where('order_id', $order->id)->where('role', 'vip_member')->value('amount'), 0.01);
        $this->assertSame('pending', CommissionEarning::where('order_id', $order->id)->where('role', 'vip_member')->value('status'));
        $this->assertNull(Wallet::where('user_id', $vip->id)->first());

        // Deliver → earnings approved + wallets credited.
        $orders->markDelivered($order->fresh());

        $this->assertSame('approved', CommissionEarning::where('order_id', $order->id)->where('role', 'vip_member')->value('status'));
        $this->assertEqualsWithDelta(200, (float) Wallet::where('user_id', $vip->id)->value('balance'), 0.01);
        $this->assertEqualsWithDelta(140, (float) Wallet::where('user_id', $cp->id)->value('balance'), 0.01);
        $this->assertEqualsWithDelta(100, (float) Wallet::where('user_id', $bm->id)->value('balance'), 0.01);

        // Delivering again must not double-credit.
        $orders->markDelivered($order->fresh());
        $this->assertEqualsWithDelta(200, (float) Wallet::where('user_id', $vip->id)->value('balance'), 0.01);
    }

    public function test_failed_online_payment_creates_no_order(): void
    {
        $this->seedRoles();
        [$vip, $microsite] = $this->makeVipMember(
            $this->makeCommissionPartner($this->makeBranchManager(30), 25),
            $this->makePlan(999),
            $this->makeCity(),
        );
        $product = Product::create(['name' => 'X', 'slug' => 'x-'.uniqid(), 'short_description' => 'x', 'price' => 500, 'status' => 'active']);

        app(CartService::class)->add($product->id, $microsite->id, 1);

        $order = app(OrderService::class)->placeFromCart(
            array_merge($this->checkoutData('online'), ['payment_outcome' => 'fail']),
            null,
        );

        $this->assertNull($order);
        $this->assertSame(0, CommissionEarning::count());
        $this->assertDatabaseCount('orders', 0);
    }
}
