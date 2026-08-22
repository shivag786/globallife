<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\PendingOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Turns a paid PendingOrder into a real Order — exactly once.
 *
 * Two callers race for this: the customer's browser (verify) and Razorpay's
 * webhook. Whichever arrives first does the work; the other gets the same order
 * back. The row is locked FOR UPDATE so they cannot both pass the status check.
 */
class RazorpayPaymentConfirmer
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    /**
     * @return Order|null  the order, or null if the pending row vanished
     */
    public function confirm(PendingOrder $pending, string $paymentId, ?string $signature = null): ?Order
    {
        [$order, $isNew] = DB::transaction(function () use ($pending, $paymentId, $signature) {
            $locked = PendingOrder::whereKey($pending->getKey())->lockForUpdate()->first();

            if (! $locked) {
                return [null, false];
            }

            // Already confirmed by the other caller — hand back the same order.
            if ($locked->status === 'completed') {
                return [$locked->order, false];
            }

            $order = $this->orders->placeFromPending($locked, [
                'razorpay_order_id' => $locked->razorpay_order_id,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            $locked->update([
                'status' => 'completed',
                'order_id' => $order->id,
                'razorpay_payment_id' => $paymentId,
                'completed_at' => now(),
            ]);

            return [$order, true];
        });

        // Outside the transaction: a slow mail server must not hold the row lock,
        // and a bounced email must not roll back a paid order.
        if ($order && $isNew) {
            try {
                Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
            } catch (\Throwable $e) {
                report($e);
            }

            Log::info('Razorpay payment confirmed', [
                'order_number' => $order->order_number,
                'razorpay_payment_id' => $paymentId,
            ]);
        }

        return $order;
    }

    /**
     * Record a failed payment. The pending row is kept for support to look at, and
     * stays out of the way of a later retry (which opens a new gateway order).
     */
    public function markFailed(PendingOrder $pending, ?string $reason = null): void
    {
        if (! $pending->isPending()) {
            return;
        }

        $pending->update([
            'status' => 'failed',
            'failure_reason' => $reason ? mb_substr($reason, 0, 255) : null,
        ]);
    }
}
