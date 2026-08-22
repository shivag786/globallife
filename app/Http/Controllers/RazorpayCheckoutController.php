<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\PendingOrder;
use App\Services\CartService;
use App\Services\RazorpayPaymentConfirmer;
use App\Services\RazorpayService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Razorpay checkout, in two AJAX steps:
 *
 *  1. create() — the browser asks for a gateway order; we price the cart
 *     server-side, open a Razorpay order for that amount and remember it in the
 *     session. Nothing is persisted yet.
 *  2. verify() — Razorpay Checkout hands the browser a signed payload; we check
 *     the HMAC against the session's order id, and only then turn the cart into
 *     a real order.
 *
 * The amount is never taken from the request — always from CartService — so a
 * tampered payload cannot buy a cart for less.
 */
class RazorpayCheckoutController extends Controller
{
    /** Remembers which gateway order this browser session opened. */
    private const SESSION_KEY = 'razorpay_pending_order_id';

    public function __construct(
        private readonly CartService $cart,
        private readonly RazorpayService $razorpay,
    ) {}

    public function create(Request $request, SettingsService $settings): JsonResponse
    {
        if (! $this->razorpay->isEnabled()) {
            return $this->fail('Online payment is currently unavailable. Please choose another payment method.');
        }

        if ($this->cart->isEmpty()) {
            return $this->fail('Your cart is empty.');
        }

        $user = Auth::user();
        if (! $user) {
            return $this->fail('Please sign in to continue.', 401);
        }

        $validated = $request->validate([
            'address_id' => ['required', 'integer'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $address = Address::where('id', $validated['address_id'])->where('user_id', $user->id)->first();
        if (! $address) {
            return $this->fail('Please choose a valid delivery address.');
        }

        $totals = $this->cart->totals();
        $amount = (float) $totals['total'];

        try {
            $rzpOrder = $this->razorpay->createOrder(
                $amount,
                'rcpt_'.Str::lower(Str::random(20)),
                ['user_id' => (string) $user->id],
            );
        } catch (RuntimeException $e) {
            report($e);

            return $this->fail($e->getMessage());
        }

        // Snapshot everything the order needs. The webhook has no session, so this
        // row - not the cart - is what turns the payment into an order.
        PendingOrder::create([
            'razorpay_order_id' => $rzpOrder['id'],
            'user_id' => $user->id,
            'customer_name' => $address->name,
            'customer_phone' => $address->phone,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'delivery_notes' => $validated['delivery_notes'] ?? null,
            'items' => $this->cart->items()->map(fn (array $item) => [
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'product_sku' => $item['product']->sku,
                'seller_id' => $item['seller_id'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
            ])->all(),
            'subtotal' => $totals['subtotal'],
            'shipping' => $totals['shipping'],
            'total' => $amount,
            'currency' => $rzpOrder['currency'],
            'status' => 'pending',
        ]);

        $request->session()->put(self::SESSION_KEY, $rzpOrder['id']);

        return response()->json([
            'ok' => true,
            'key' => $this->razorpay->keyId(),
            'order_id' => $rzpOrder['id'],
            'amount' => $rzpOrder['amount'],
            'currency' => $rzpOrder['currency'],
            'name' => $settings->get('site_title') ?: config('app.name'),
            'description' => 'Order payment',
            'prefill' => [
                'name' => $address->name,
                'email' => (string) $user->email,
                'contact' => (string) $address->phone,
            ],
        ]);
    }

    public function verify(Request $request, RazorpayPaymentConfirmer $confirm): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->fail('Please sign in to continue.', 401);
        }

        $payload = $request->validate([
            'razorpay_order_id' => ['required', 'string', 'max:100'],
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);

        $pending = PendingOrder::where('razorpay_order_id', $payload['razorpay_order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $pending) {
            return $this->fail('This payment session has expired. Please try again.');
        }

        if (! $this->razorpay->verifySignature(
            $payload['razorpay_order_id'],
            $payload['razorpay_payment_id'],
            $payload['razorpay_signature'],
        )) {
            return $this->fail('We could not verify this payment. If money was deducted it will be refunded automatically.');
        }

        // The webhook may have confirmed this already; confirm() is idempotent and
        // hands back the order either way.
        $order = $confirm->confirm($pending, $payload['razorpay_payment_id'], $payload['razorpay_signature']);

        if (! $order) {
            return $this->fail('We could not record your order. Please contact support with payment id '.$payload['razorpay_payment_id'].'.');
        }

        $this->cart->clear();
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->put('recent_order_id', $order->id);

        return response()->json([
            'ok' => true,
            'redirect' => route('checkout.confirmation', $order),
        ]);
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message], $status);
    }
}
