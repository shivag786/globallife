<?php

namespace Tests\Feature;

use App\Models\CommissionEarning;
use App\Models\CommissionPayout;
use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\VipActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * Super-admin monthly settlement of Commission Partner earnings: the month
 * report pulls from BOTH commission systems, and marking a month paid clears
 * the withdrawable amount without touching what is still pending.
 */
class PartnerPayoutTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private function superAdmin(): User
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('super_admin');

        return $admin;
    }

    private function rule(string $role, float $value): void
    {
        CommissionRule::create([
            'scope' => 'global', 'scope_id' => 0, 'role' => $role,
            'type' => 'percent', 'value' => $value, 'status' => 'active',
        ]);
    }

    private function product(string $name, float $price): Product
    {
        return Product::create([
            'name' => $name, 'slug' => Str::slug($name).'-'.uniqid(),
            'short_description' => 'x', 'price' => $price, 'status' => 'active',
        ]);
    }

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'John Buyer',
            'customer_phone' => '9998887776',
            'address' => '12 Test Street',
            'city' => 'Jhansi',
            'state' => 'UP',
            'pincode' => '284001',
            'payment_method' => 'cod',
            'payment_outcome' => 'success',
        ];
    }

    public function test_month_report_combines_product_and_vip_commission_and_mark_paid_clears_only_the_payable(): void
    {
        $this->seedRoles();
        $period = now()->format('Y-m');

        $bm = $this->makeBranchManager(30);
        $cp = $this->makeCommissionPartner($bm, 25);
        $cp->update(['name' => 'Priya Partner']);
        [, $micrositeA] = $this->makeVipMember($cp, $this->makePlan(1000), $this->makeCity());
        [, $micrositeB] = $this->makeVipMember($cp, $this->makePlan(2000), $this->makeCity());

        // VIP-activation commission: 25% of ₹1000 = ₹250 for the partner.
        app(VipActivationService::class)->activate($micrositeA, $cp);

        $this->rule('commission_partner', 10);

        // Delivered order → ₹100 approved product commission (in the wallet).
        $cart = app(CartService::class);
        $orders = app(OrderService::class);
        $customer = User::factory()->create(['status' => 'active']);

        $cart->add($this->product('Delivered Box', 1000)->id, $micrositeA->id, 1);
        $delivered = $orders->placeFromCart($this->checkoutData(), $customer);
        $orders->markDelivered($delivered->fresh());

        // Undelivered order → ₹50 still pending, must survive the payout untouched.
        $cart->add($this->product('Pending Box', 500)->id, $micrositeB->id, 1);
        $orders->placeFromCart($this->checkoutData(), $customer);

        $this->assertEqualsWithDelta(100, (float) Wallet::where('user_id', $cp->id)->value('balance'), 0.01);

        $admin = $this->superAdmin();

        // The list shows the month's split for the partner.
        $this->actingAs($admin)->get('/admin/partner-payouts?period='.$period)
            ->assertOk()
            ->assertSee('Priya Partner')
            ->assertSee('350.00');   // ₹100 product + ₹250 VIP = earned & payable

        // The detail page carries both ledgers.
        $this->actingAs($admin)->get('/admin/partner-payouts/'.$cp->id.'?period='.$period)
            ->assertOk()
            ->assertSee($micrositeA->business_name)
            ->assertSee('Delivered Box')
            ->assertSee('Pending Box');

        $this->actingAs($admin)
            ->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => $period])
            ->assertRedirect();

        $payout = CommissionPayout::where('user_id', $cp->id)->where('period', $period)->sole();
        $this->assertEqualsWithDelta(100, (float) $payout->product_amount, 0.01);
        $this->assertEqualsWithDelta(250, (float) $payout->vip_amount, 0.01);
        $this->assertEqualsWithDelta(350, (float) $payout->amount, 0.01);

        // Withdrawable is now zero; the pending ₹50 is still pending.
        $this->assertEqualsWithDelta(0, (float) Wallet::where('user_id', $cp->id)->value('balance'), 0.01);
        $this->assertEqualsWithDelta(
            50,
            (float) CommissionEarning::where('beneficiary_id', $cp->id)->where('status', 'pending')->sum('amount'),
            0.01,
        );

        // Paying the same month again settles nothing.
        $this->actingAs($admin)
            ->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => $period])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, CommissionPayout::where('user_id', $cp->id)->count());
    }

    public function test_a_late_delivery_inside_a_settled_month_becomes_payable_again(): void
    {
        $this->seedRoles();
        $period = now()->format('Y-m');

        $cp = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [, $microsite] = $this->makeVipMember($cp, $this->makePlan(1000), $this->makeCity());
        $this->rule('commission_partner', 10);

        $cart = app(CartService::class);
        $orders = app(OrderService::class);
        $customer = User::factory()->create(['status' => 'active']);

        $cart->add($this->product('First Box', 1000)->id, $microsite->id, 1);
        $first = $orders->placeFromCart($this->checkoutData(), $customer);
        $orders->markDelivered($first->fresh());

        $cart->add($this->product('Second Box', 2000)->id, $microsite->id, 1);
        $second = $orders->placeFromCart($this->checkoutData(), $customer);

        $admin = $this->superAdmin();
        $this->actingAs($admin)->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => $period]);

        // ₹100 settled, wallet emptied.
        $this->assertEqualsWithDelta(0, (float) Wallet::where('user_id', $cp->id)->value('balance'), 0.01);

        // The second order lands later in the same month → ₹200 payable again.
        $orders->markDelivered($second->fresh());
        $this->assertEqualsWithDelta(200, (float) Wallet::where('user_id', $cp->id)->value('balance'), 0.01);

        $this->actingAs($admin)
            ->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => $period])
            ->assertSessionHas('status');

        $this->assertEqualsWithDelta(300, (float) CommissionPayout::where('user_id', $cp->id)->sum('amount'), 0.01);
        $this->assertEqualsWithDelta(0, (float) Wallet::where('user_id', $cp->id)->value('balance'), 0.01);
    }

    public function test_a_different_month_is_not_settled_by_this_months_payout(): void
    {
        $this->seedRoles();

        $cp = $this->makeCommissionPartner($this->makeBranchManager(30), 25);
        [, $microsite] = $this->makeVipMember($cp, $this->makePlan(1000), $this->makeCity());
        $this->rule('commission_partner', 10);

        $cart = app(CartService::class);
        $orders = app(OrderService::class);
        $customer = User::factory()->create(['status' => 'active']);

        $cart->add($this->product('Last Month Box', 1000)->id, $microsite->id, 1);
        $old = $orders->placeFromCart($this->checkoutData(), $customer);
        $orders->markDelivered($old->fresh());

        $lastMonth = now()->subMonthNoOverflow();
        CommissionEarning::where('order_id', $old->id)->update([
            'created_at' => $lastMonth->copy()->startOfMonth()->addDays(3),
        ]);

        $admin = $this->superAdmin();

        // Nothing in the current month.
        $this->actingAs($admin)
            ->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => now()->format('Y-m')])
            ->assertSessionHas('error');

        // Last month still owes ₹100.
        $this->actingAs($admin)
            ->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => $lastMonth->format('Y-m')])
            ->assertSessionHas('status');

        $this->assertEqualsWithDelta(
            100,
            (float) CommissionPayout::where('user_id', $cp->id)->where('period', $lastMonth->format('Y-m'))->sum('amount'),
            0.01,
        );
    }

    public function test_only_super_admin_reaches_the_payout_screens(): void
    {
        $this->seedRoles();
        $cp = $this->makeCommissionPartner($this->makeBranchManager(30), 25);

        $this->actingAs($cp)->get('/admin/partner-payouts')->assertForbidden();
        $this->actingAs($cp)->post('/admin/partner-payouts/'.$cp->id.'/mark-paid', ['period' => now()->format('Y-m')])
            ->assertForbidden();

        $this->assertSame(0, CommissionPayout::count());
    }

    public function test_a_non_partner_user_is_not_a_valid_payout_target(): void
    {
        $this->seedRoles();
        $admin = $this->superAdmin();
        $bm = $this->makeBranchManager(30);

        $this->actingAs($admin)->get('/admin/partner-payouts/'.$bm->id)->assertNotFound();
    }
}
