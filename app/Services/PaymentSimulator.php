<?php

namespace App\Services;

/**
 * A stand-in payment gateway for testing. Real gateways (Razorpay/Stripe/etc.)
 * slot in behind this same interface later without touching the checkout flow.
 */
class PaymentSimulator
{
    /**
     * Simulate a charge. Cash-on-delivery is always accepted (collected later);
     * for an online charge, the caller-selected outcome decides success/failure.
     */
    public function charge(string $method, string $outcome = 'success'): bool
    {
        if ($method === 'cod') {
            return true;
        }

        return $outcome === 'success';
    }
}
