<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlacedMail;
use App\Models\Address;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
    private const SESSION_KEY = 'razorpay_pending';

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

        $request->session()->put(self::SESSION_KEY, [
            'razorpay_order_id' => $rzpOrder['id'],
            'address_id' => $address->id,
            'delivery_notes' => $validated['delivery_notes'] ?? null,
            'amount' => $amount,
        ]);

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

    public function verify(Request $request, OrderService $orders): JsonResponse
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

        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending || $pending['razorpay_order_id'] !== $payload['razorpay_order_id']) {
            return $this->fail('This payment session has expired. Please try again.');
        }

        if (! $this->razorpay->verifySignature(
            $payload['razorpay_order_id'],
            $payload['razorpay_payment_id'],
            $payload['razorpay_signature'],
        )) {
            $request->session()->forget(self::SESSION_KEY);

            return $this->fail('We could not verify this payment. If money was deducted it will be refunded automatically.');
        }

        if ($this->cart->isEmpty()) {
            $request->session()->forget(self::SESSION_KEY);

            return $this->fail('Your cart is empty.');
        }

        $address = Address::where('id', $pending['address_id'])->where('user_id', $user->id)->first();
        if (! $address) {
            return $this->fail('Please choose a valid delivery address.');
        }

        $order = $orders->placeFromCart([
            'customer_name' => $address->name,
            'customer_phone' => $address->phone,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'delivery_notes' => $pending['delivery_notes'] ?? null,
            'payment_method' => 'online',
            'payment_gateway' => 'razorpay',
            'razorpay_order_id' => $payload['razorpay_order_id'],
            'razorpay_payment_id' => $payload['razorpay_payment_id'],
            'razorpay_signature' => $payload['razorpay_signature'],
        ], $user);

        if (! $order) {
            return $this->fail('We could not record your order. Please contact support with payment id '.$payload['razorpay_payment_id'].'.');
        }

        $request->session()->forget(self::SESSION_KEY);
        $request->session()->put('recent_order_id', $order->id);

        try {
            Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
        } catch (\Throwable $e) {
            report($e);
        }

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
