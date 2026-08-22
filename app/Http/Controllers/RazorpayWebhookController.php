<?php

namespace App\Http\Controllers;

use App\Models\PendingOrder;
use App\Services\RazorpayPaymentConfirmer;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay server-to-server webhook: https://globallife.co.in/webhooks/razorpay
 *
 * This is the safety net for a payment whose browser never came back — the tab
 * was closed, the connection dropped, the phone died. Razorpay still tells us the
 * money was captured, so the order gets created anyway.
 *
 * Razorpay retries anything that is not a 2xx, and may deliver the same event more
 * than once, so every handler here is idempotent. Unknown events are acknowledged
 * rather than rejected, otherwise Razorpay retries them forever and eventually
 * disables the webhook.
 */
class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayService $razorpay, RazorpayPaymentConfirmer $confirm): JsonResponse
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        // Must be the RAW body — a re-encoded array would not match the HMAC.
        if (! $razorpay->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Razorpay webhook rejected: bad signature', [
                'event' => $request->input('event'),
                'ip' => $request->ip(),
            ]);

            return response()->json(['ok' => false], 400);
        }

        $event = (string) $request->input('event');
        $payment = $request->input('payload.payment.entity', []);
        $razorpayOrderId = $payment['order_id'] ?? null;

        if (! $razorpayOrderId) {
            return $this->ack('no order id on event '.$event);
        }

        $pending = PendingOrder::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $pending) {
            // Not ours, or the checkout row was pruned. Acknowledge so Razorpay stops.
            Log::warning('Razorpay webhook for unknown order', ['razorpay_order_id' => $razorpayOrderId]);

            return $this->ack('unknown order');
        }

        return match ($event) {
            'payment.captured', 'order.paid' => $this->handleCaptured($pending, $payment, $confirm),
            'payment.failed' => $this->handleFailed($pending, $payment, $confirm),
            default => $this->ack('ignored event '.$event),
        };
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function handleCaptured(PendingOrder $pending, array $payment, RazorpayPaymentConfirmer $confirm): JsonResponse
    {
        $paymentId = $payment['id'] ?? null;

        if (! $paymentId) {
            return $this->ack('no payment id');
        }

        // Signature is null here: this delivery was authenticated by the webhook
        // HMAC above, not by the browser handler's per-payment signature.
        $order = $confirm->confirm($pending, $paymentId);

        return response()->json([
            'ok' => true,
            'order_number' => $order?->order_number,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payment
     */
    private function handleFailed(PendingOrder $pending, array $payment, RazorpayPaymentConfirmer $confirm): JsonResponse
    {
        $confirm->markFailed($pending, $payment['error_description'] ?? $payment['error_reason'] ?? 'Payment failed');

        return $this->ack('marked failed');
    }

    private function ack(string $reason): JsonResponse
    {
        return response()->json(['ok' => true, 'reason' => $reason]);
    }
}
