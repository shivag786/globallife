<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin wrapper over the Razorpay REST API. Credentials come from the admin
 * Payment Gateway settings (see PaymentGatewayService), never from env, so the
 * keys can be rotated from the panel without a deploy.
 *
 * Flow: createOrder() on the server → Razorpay Checkout collects the money in
 * the browser → verifySignature() proves the handler payload really came from
 * Razorpay before we persist anything.
 */
class RazorpayService
{
    private const BASE_URL = 'https://api.razorpay.com/v1';

    public function __construct(private readonly PaymentGatewayService $gateway) {}

    public function isEnabled(): bool
    {
        return $this->gateway->razorpayEnabled();
    }

    public function keyId(): ?string
    {
        return $this->gateway->razorpayKeyId();
    }

    /**
     * Create a Razorpay order for the given rupee amount. Razorpay works in the
     * smallest currency unit, so rupees are converted to paise here.
     *
     * @param  array<string, string>  $notes
     * @return array<string, mixed> the Razorpay order payload (id, amount, currency, …)
     *
     * @throws RuntimeException when the gateway is unconfigured or rejects the call
     */
    public function createOrder(float $amount, string $receipt, array $notes = []): array
    {
        $keyId = $this->gateway->razorpayKeyId();
        $secret = $this->gateway->razorpayKeySecret();

        if (! $this->isEnabled() || ! $keyId || ! $secret) {
            throw new RuntimeException('Razorpay is not configured.');
        }

        $response = Http::withBasicAuth($keyId, $secret)
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post(self::BASE_URL.'/orders', [
                'amount' => $this->toPaise($amount),
                'currency' => $this->gateway->currency(),
                'receipt' => $receipt,
                'payment_capture' => 1,
                'notes' => $notes,
            ]);

        if ($response->failed()) {
            Log::error('Razorpay order creation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new RuntimeException($this->errorMessage($response->json()));
        }

        return $response->json();
    }

    /**
     * Verify the Checkout handler payload: HMAC-SHA256 of "<order_id>|<payment_id>"
     * keyed with the secret must equal the signature Razorpay returned.
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        $secret = $this->gateway->razorpayKeySecret();

        if (! $secret || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $razorpayOrderId.'|'.$razorpayPaymentId, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Verify a webhook delivery: HMAC-SHA256 of the RAW request body keyed with the
     * webhook secret must equal the X-Razorpay-Signature header.
     *
     * Note this uses the webhook secret, which is a different value from the API
     * key secret used by verifySignature().
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = $this->gateway->razorpayWebhookSecret();

        if (! $secret || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $secret), $signature);
    }

    /**
     * Fetch a payment from Razorpay. Returns null when the call fails so callers
     * can fall back to signature verification alone.
     *
     * @return array<string, mixed>|null
     */
    public function fetchPayment(string $paymentId): ?array
    {
        $keyId = $this->gateway->razorpayKeyId();
        $secret = $this->gateway->razorpayKeySecret();

        if (! $keyId || ! $secret) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($keyId, $secret)
                ->acceptJson()
                ->timeout(20)
                ->get(self::BASE_URL.'/payments/'.$paymentId);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Ping the API with the saved credentials so admin can confirm they work
     * before switching the gateway live.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        $keyId = $this->gateway->razorpayKeyId();
        $secret = $this->gateway->razorpayKeySecret();

        if (! $keyId || ! $secret) {
            return ['ok' => false, 'message' => 'Add both the Key ID and Key Secret first.'];
        }

        try {
            $response = Http::withBasicAuth($keyId, $secret)
                ->acceptJson()
                ->timeout(20)
                ->get(self::BASE_URL.'/payments', ['count' => 1]);
        } catch (\Throwable $e) {
            report($e);

            return ['ok' => false, 'message' => 'Could not reach Razorpay: '.$e->getMessage()];
        }

        if ($response->successful()) {
            return ['ok' => true, 'message' => 'Connection successful — these credentials are valid.'];
        }

        if ($response->status() === 401) {
            return ['ok' => false, 'message' => 'Razorpay rejected these credentials (401). Check the Key ID and Secret.'];
        }

        return ['ok' => false, 'message' => $this->errorMessage($response->json())];
    }

    public function toPaise(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function errorMessage(?array $body): string
    {
        return $body['error']['description'] ?? 'Razorpay rejected the request. Please try again.';
    }
}
