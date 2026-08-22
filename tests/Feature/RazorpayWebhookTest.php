<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PendingOrder;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The webhook is the safety net for a payment whose browser never came back.
 * It must: reject anything unsigned, create the order from the snapshot (not the
 * session cart), and stay idempotent no matter how often Razorpay redelivers.
 */
class RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_123';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Mail::fake();

        $settings = app(SettingsService::class);
        $settings->set(PaymentGatewayService::KEY_ENABLED, '1');
        $settings->set(PaymentGatewayService::KEY_ID, 'rzp_test_ABC123');
        app(PaymentGatewayService::class)->setRazorpayKeySecret('key_secret');
        app(PaymentGatewayService::class)->setRazorpayWebhookSecret(self::WEBHOOK_SECRET);
        Cache::flush();
    }

    private function pendingOrder(string $razorpayOrderId = 'order_TEST123'): PendingOrder
    {
        Role::findOrCreate('customer', 'web');
        $user = User::create([
            'name' => 'Buyer', 'email' => 'buyer'.uniqid().'@example.com',
            'mobile' => '9999999999', 'password' => bcrypt('secret1234'), 'status' => 'active',
        ]);
        $user->assignRole('customer');

        $product = Product::create([
            'name' => 'Wellness Box', 'slug' => 'wellness-'.uniqid(),
            'short_description' => 'x', 'price' => 500, 'status' => 'active',
        ]);

        return PendingOrder::create([
            'razorpay_order_id' => $razorpayOrderId,
            'user_id' => $user->id,
            'customer_name' => 'Jane', 'customer_phone' => '9999999999',
            'address' => '12 Test St', 'city' => 'Jhansi', 'state' => 'UP', 'pincode' => '284001',
            'delivery_notes' => null,
            'items' => [[
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'seller_id' => null,
                'unit_price' => 500.0,
                'quantity' => 1,
                'line_total' => 500.0,
            ]],
            'subtotal' => 500, 'shipping' => 0, 'total' => 500, 'currency' => 'INR',
            'status' => 'pending',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function deliver(array $payload, ?string $secret = self::WEBHOOK_SECRET): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $secret ?? 'wrong');

        return $this->call(
            'POST',
            route('webhooks.razorpay'),
            [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_RAZORPAY_SIGNATURE' => $signature],
            $body,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function capturedEvent(string $razorpayOrderId = 'order_TEST123', string $paymentId = 'pay_TEST999'): array
    {
        return [
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => [
                'id' => $paymentId,
                'order_id' => $razorpayOrderId,
                'amount' => 50000,
                'status' => 'captured',
            ]]],
        ];
    }

    public function test_webhook_rejects_a_wrongly_signed_delivery(): void
    {
        $pending = $this->pendingOrder();

        $this->deliver($this->capturedEvent(), 'attacker-secret')->assertStatus(400);

        $this->assertSame(0, Order::count());
        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_webhook_rejects_a_delivery_with_no_signature_header(): void
    {
        $this->pendingOrder();

        $this->postJson(route('webhooks.razorpay'), $this->capturedEvent())->assertStatus(400);

        $this->assertSame(0, Order::count());
    }

    public function test_captured_payment_creates_the_order_from_the_snapshot(): void
    {
        $pending = $this->pendingOrder();

        $this->deliver($this->capturedEvent())->assertOk();

        $order = Order::first();
        $this->assertNotNull($order, 'The webhook should have created the order.');
        $this->assertSame('razorpay', $order->payment_gateway);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('order_TEST123', $order->razorpay_order_id);
        $this->assertSame('pay_TEST999', $order->razorpay_payment_id);
        $this->assertSame('500.00', $order->total);
        $this->assertSame('Jane', $order->customer_name);

        $this->assertCount(1, $order->items);
        $this->assertSame('Wellness Box', $order->items->first()->product_name);

        $pending->refresh();
        $this->assertSame('completed', $pending->status);
        $this->assertSame($order->id, $pending->order_id);
        $this->assertNotNull($pending->completed_at);
    }

    public function test_redelivery_of_the_same_event_does_not_create_a_second_order(): void
    {
        $this->pendingOrder();

        $this->deliver($this->capturedEvent())->assertOk();
        $this->deliver($this->capturedEvent())->assertOk();
        $this->deliver($this->capturedEvent())->assertOk();

        $this->assertSame(1, Order::count(), 'Razorpay redelivers events; only one order may exist.');
    }

    public function test_browser_verify_after_the_webhook_reuses_the_same_order(): void
    {
        $pending = $this->pendingOrder();

        // Webhook wins the race (customer's browser was closed).
        $this->deliver($this->capturedEvent())->assertOk();
        $order = Order::first();

        // Customer reopens the tab and the handler finally posts back.
        $signature = hash_hmac('sha256', 'order_TEST123|pay_TEST999', 'key_secret');

        $this->actingAs($pending->user)
            ->postJson(route('checkout.razorpay.verify'), [
                'razorpay_order_id' => 'order_TEST123',
                'razorpay_payment_id' => 'pay_TEST999',
                'razorpay_signature' => $signature,
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(1, Order::count());
        $this->assertSame($order->id, Order::first()->id);
    }

    public function test_failed_payment_marks_the_pending_order_failed_without_an_order(): void
    {
        $pending = $this->pendingOrder();

        $this->deliver([
            'event' => 'payment.failed',
            'payload' => ['payment' => ['entity' => [
                'id' => 'pay_FAIL1',
                'order_id' => 'order_TEST123',
                'error_description' => 'Card declined by issuer',
            ]]],
        ])->assertOk();

        $this->assertSame(0, Order::count());
        $pending->refresh();
        $this->assertSame('failed', $pending->status);
        $this->assertSame('Card declined by issuer', $pending->failure_reason);
    }

    public function test_event_for_an_unknown_order_is_acknowledged_not_retried(): void
    {
        // A 2xx stops Razorpay retrying something we will never be able to handle.
        $this->deliver($this->capturedEvent('order_NOT_OURS'))->assertOk();

        $this->assertSame(0, Order::count());
    }

    public function test_unrelated_event_types_are_acknowledged_and_ignored(): void
    {
        $pending = $this->pendingOrder();

        $this->deliver([
            'event' => 'refund.created',
            'payload' => ['payment' => ['entity' => [
                'id' => 'pay_TEST999', 'order_id' => 'order_TEST123',
            ]]],
        ])->assertOk();

        $this->assertSame(0, Order::count());
        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_webhook_is_exempt_from_csrf(): void
    {
        // The route must work without a session/token, which the deliveries above
        // already rely on - assert it explicitly so the exemption is not dropped.
        $this->pendingOrder();

        $this->deliver($this->capturedEvent())->assertOk();

        $this->assertSame(1, Order::count());
    }
}
