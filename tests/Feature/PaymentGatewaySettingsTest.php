<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\PaymentGatewayService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Admin configures Razorpay (keys + toggle); checkout then offers exactly the
 * payment methods those settings allow, and a Razorpay payment only becomes an
 * order once its signature verifies.
 */
class PaymentGatewaySettingsTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test_secret_key';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Mail::fake();
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin', 'web');
        $user = User::create([
            'name' => 'Root', 'email' => 'root'.uniqid().'@example.com',
            'password' => bcrypt('secret1234'), 'status' => 'active',
        ]);
        $user->assignRole('super_admin');

        return $user;
    }

    private function customerWithCart(): User
    {
        $product = Product::create([
            'name' => 'Wellness Box', 'slug' => 'wellness-'.uniqid(),
            'short_description' => 'x', 'price' => 500, 'status' => 'active',
        ]);
        app(CartService::class)->add($product->id, null, 1);

        Role::findOrCreate('customer', 'web');
        $user = User::create([
            'name' => 'Buyer', 'email' => 'buyer'.uniqid().'@example.com',
            'mobile' => '9999999999', 'password' => bcrypt('secret1234'), 'status' => 'active',
        ]);
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

    private function enableRazorpay(): void
    {
        $settings = app(SettingsService::class);
        $settings->set(PaymentGatewayService::KEY_ENABLED, '1');
        $settings->set(PaymentGatewayService::KEY_ID, 'rzp_test_ABC123');
        app(PaymentGatewayService::class)->setRazorpayKeySecret(self::SECRET);
        Cache::flush();
    }

    public function test_super_admin_can_save_razorpay_settings_and_secret_is_encrypted(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('admin.settings.payment.update'), [
                'razorpay_enabled' => '1',
                'razorpay_mode' => 'test',
                'razorpay_key_id' => 'rzp_test_ABC123',
                'razorpay_key_secret' => self::SECRET,
                'razorpay_currency' => 'inr',
                'cod_enabled' => '1',
                'payment_test_mode' => '0',
            ])
            ->assertRedirect(route('admin.settings.payment.edit'));

        Cache::flush();
        $gateway = app(PaymentGatewayService::class);

        $this->assertTrue($gateway->razorpayEnabled());
        $this->assertSame('rzp_test_ABC123', $gateway->razorpayKeyId());
        $this->assertSame('INR', $gateway->currency());
        $this->assertSame(self::SECRET, $gateway->razorpayKeySecret());

        $stored = Setting::where('key', PaymentGatewayService::KEY_SECRET)->value('value');
        $this->assertNotSame(self::SECRET, $stored, 'The secret must not be stored in plain text.');
        $this->assertSame(self::SECRET, Crypt::decryptString($stored));
    }

    public function test_blank_secret_keeps_the_saved_one(): void
    {
        $this->enableRazorpay();

        $this->actingAs($this->superAdmin())
            ->put(route('admin.settings.payment.update'), [
                'razorpay_enabled' => '1',
                'razorpay_mode' => 'live',
                'razorpay_key_id' => 'rzp_live_XYZ',
                'razorpay_key_secret' => '',
                'razorpay_currency' => 'INR',
                'cod_enabled' => '1',
                'payment_test_mode' => '0',
            ])->assertSessionHasNoErrors();

        Cache::flush();
        $this->assertSame(self::SECRET, app(PaymentGatewayService::class)->razorpayKeySecret());
        $this->assertSame('live', app(PaymentGatewayService::class)->razorpayMode());
    }

    public function test_enabling_razorpay_without_keys_is_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('admin.settings.payment.update'), [
                'razorpay_enabled' => '1',
                'razorpay_mode' => 'test',
                'razorpay_key_id' => '',
                'razorpay_key_secret' => '',
                'razorpay_currency' => 'INR',
                'cod_enabled' => '1',
                'payment_test_mode' => '0',
            ])
            ->assertSessionHasErrors(['razorpay_key_id', 'razorpay_key_secret']);

        $this->assertFalse(app(PaymentGatewayService::class)->razorpayEnabled());
    }

    public function test_non_super_admin_cannot_open_payment_settings(): void
    {
        Role::findOrCreate('admin', 'web');
        $user = User::create([
            'name' => 'Staff', 'email' => 'staff'.uniqid().'@example.com',
            'password' => bcrypt('secret1234'), 'status' => 'active',
        ]);
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('admin.settings.payment.edit'))->assertForbidden();
    }

    public function test_checkout_shows_razorpay_only_when_enabled(): void
    {
        $user = $this->customerWithCart();
        $this->address($user);

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertDontSee('Pay Online')
            ->assertSee('Cash on Delivery');

        $this->enableRazorpay();

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Pay Online')
            ->assertSee('checkout.razorpay.com', false);
    }

    public function test_razorpay_choice_is_rejected_while_the_gateway_is_off(): void
    {
        $user = $this->customerWithCart();
        $address = $this->address($user);

        $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_choice' => 'razorpay',
        ])->assertSessionHasErrors('payment_choice');

        $this->assertSame(0, Order::count());
    }

    public function test_create_order_calls_razorpay_with_the_cart_total_in_paise(): void
    {
        $user = $this->customerWithCart();
        $address = $this->address($user);
        $this->enableRazorpay();

        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_TEST123', 'amount' => 50000, 'currency' => 'INR',
            ]),
        ]);

        $total = app(CartService::class)->totals()['total'];

        $this->actingAs($user)
            ->postJson(route('checkout.razorpay.create'), ['address_id' => $address->id])
            ->assertOk()
            ->assertJson(['ok' => true, 'order_id' => 'order_TEST123', 'key' => 'rzp_test_ABC123']);

        Http::assertSent(fn ($request) => $request['amount'] === (int) round($total * 100)
            && $request['currency'] === 'INR');
    }

    public function test_verify_creates_the_order_for_a_valid_signature(): void
    {
        $user = $this->customerWithCart();
        $address = $this->address($user);
        $this->enableRazorpay();

        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_TEST123', 'amount' => 50000, 'currency' => 'INR',
            ]),
        ]);

        $this->actingAs($user)
            ->postJson(route('checkout.razorpay.create'), ['address_id' => $address->id])
            ->assertOk();

        $signature = hash_hmac('sha256', 'order_TEST123|pay_TEST123', self::SECRET);

        $this->actingAs($user)
            ->postJson(route('checkout.razorpay.verify'), [
                'razorpay_order_id' => 'order_TEST123',
                'razorpay_payment_id' => 'pay_TEST123',
                'razorpay_signature' => $signature,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $order = Order::firstOrFail();
        $this->assertSame('online', $order->payment_method);
        $this->assertSame('razorpay', $order->payment_gateway);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pay_TEST123', $order->razorpay_payment_id);
        $this->assertSame('order_TEST123', $order->razorpay_order_id);
        $this->assertTrue(app(CartService::class)->isEmpty());
    }

    public function test_verify_rejects_a_tampered_signature_and_creates_no_order(): void
    {
        $user = $this->customerWithCart();
        $address = $this->address($user);
        $this->enableRazorpay();

        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_TEST123', 'amount' => 50000, 'currency' => 'INR',
            ]),
        ]);

        $this->actingAs($user)
            ->postJson(route('checkout.razorpay.create'), ['address_id' => $address->id])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('checkout.razorpay.verify'), [
                'razorpay_order_id' => 'order_TEST123',
                'razorpay_payment_id' => 'pay_TEST123',
                'razorpay_signature' => str_repeat('a', 64),
            ])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertSame(0, Order::count());
        $this->assertFalse(app(CartService::class)->isEmpty());
    }

    public function test_verify_without_a_pending_session_is_refused(): void
    {
        $user = $this->customerWithCart();
        $this->address($user);
        $this->enableRazorpay();

        $signature = hash_hmac('sha256', 'order_TEST123|pay_TEST123', self::SECRET);

        $this->actingAs($user)
            ->postJson(route('checkout.razorpay.verify'), [
                'razorpay_order_id' => 'order_TEST123',
                'razorpay_payment_id' => 'pay_TEST123',
                'razorpay_signature' => $signature,
            ])
            ->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_cod_can_be_switched_off_when_razorpay_is_live(): void
    {
        $this->enableRazorpay();
        app(SettingsService::class)->set(PaymentGatewayService::KEY_COD, '0');
        Cache::flush();

        $this->assertSame(['razorpay'], app(PaymentGatewayService::class)->allowedChoices());
    }

    public function test_cod_is_the_fallback_when_every_method_is_off(): void
    {
        $settings = app(SettingsService::class);
        $settings->set(PaymentGatewayService::KEY_ENABLED, '0');
        $settings->set(PaymentGatewayService::KEY_COD, '0');
        $settings->set(PaymentGatewayService::KEY_TEST_MODE, '0');
        Cache::flush();

        $this->assertSame(['cod'], app(PaymentGatewayService::class)->allowedChoices());
    }
}
