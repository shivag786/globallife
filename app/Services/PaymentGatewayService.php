<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Admin-controlled payment configuration: which methods the storefront offers
 * and the Razorpay credentials behind them. Everything lives in the `settings`
 * table (SettingsService), so the panel is the single source of truth.
 *
 * The secret key is encrypted at rest — it is the only setting that is.
 */
class PaymentGatewayService
{
    public const KEY_ENABLED = 'razorpay_enabled';

    public const KEY_MODE = 'razorpay_mode';

    public const KEY_ID = 'razorpay_key_id';

    public const KEY_SECRET = 'razorpay_key_secret';

    public const KEY_WEBHOOK_SECRET = 'razorpay_webhook_secret';

    public const KEY_CURRENCY = 'razorpay_currency';

    public const KEY_COD = 'cod_enabled';

    public const KEY_TEST_MODE = 'payment_test_mode';

    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Razorpay is only live when the toggle is on AND both keys are present —
     * a half-configured gateway must never be shown to a customer.
     */
    public function razorpayEnabled(): bool
    {
        return $this->flag(self::KEY_ENABLED)
            && filled($this->razorpayKeyId())
            && filled($this->razorpayKeySecret());
    }

    /**
     * The raw admin toggle, ignoring whether the keys are filled in. Used by the
     * settings screen to warn "enabled but not configured".
     */
    public function razorpayToggledOn(): bool
    {
        return $this->flag(self::KEY_ENABLED);
    }

    public function razorpayMode(): string
    {
        return $this->settings->get(self::KEY_MODE) === 'live' ? 'live' : 'test';
    }

    public function razorpayKeyId(): ?string
    {
        return $this->settings->get(self::KEY_ID) ?: null;
    }

    public function razorpayKeySecret(): ?string
    {
        return $this->decryptSetting(self::KEY_SECRET);
    }

    private function decryptSetting(string $key): ?string
    {
        $stored = $this->settings->get($key);

        if (! $stored) {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException) {
            // Value predates encryption (or was seeded by hand) — use it as-is.
            return $stored;
        }
    }

    public function setRazorpayKeySecret(?string $secret): void
    {
        $this->settings->set(self::KEY_SECRET, filled($secret) ? Crypt::encryptString($secret) : null);
    }

    /**
     * Webhook signing secret — set when creating the webhook in the Razorpay
     * dashboard. Distinct from the API key secret, and stored encrypted the same way.
     */
    public function razorpayWebhookSecret(): ?string
    {
        return $this->decryptSetting(self::KEY_WEBHOOK_SECRET);
    }

    public function setRazorpayWebhookSecret(?string $secret): void
    {
        $this->settings->set(self::KEY_WEBHOOK_SECRET, filled($secret) ? Crypt::encryptString($secret) : null);
    }

    public function webhookConfigured(): bool
    {
        return filled($this->razorpayWebhookSecret());
    }

    public function currency(): string
    {
        return strtoupper($this->settings->get(self::KEY_CURRENCY) ?: 'INR');
    }

    public function codEnabled(): bool
    {
        // Default ON: an install that never touched these settings keeps working.
        return $this->flag(self::KEY_COD, true);
    }

    /**
     * Test-payment radio buttons (the PaymentSimulator path). Off unless admin
     * turns them on — they are only useful while no real gateway is wired up.
     */
    public function testModeEnabled(): bool
    {
        return $this->flag(self::KEY_TEST_MODE);
    }

    /**
     * Payment choices the checkout page may render, keyed by the `payment_choice`
     * value the form posts back. Never returns an empty list — Cash on Delivery
     * is the fallback so checkout can always complete.
     *
     * @return array<string, array{label: string, description: string}>
     */
    public function options(): array
    {
        $options = [];

        if ($this->razorpayEnabled()) {
            $options['razorpay'] = [
                'label' => 'Pay Online — Card / UPI / Netbanking / Wallet',
                'description' => 'Secure payment powered by Razorpay.',
            ];
        }

        if ($this->codEnabled()) {
            $options['cod'] = [
                'label' => 'Cash on Delivery',
                'description' => 'Pay in cash when your order arrives.',
            ];
        }

        if ($this->testModeEnabled()) {
            $options['online_success'] = [
                'label' => 'Test Payment — Success',
                'description' => 'Simulates a successful online payment.',
            ];
            $options['online_fail'] = [
                'label' => 'Test Payment — Failure',
                'description' => 'Simulates a declined payment (to test the flow).',
            ];
        }

        if ($options === []) {
            $options['cod'] = [
                'label' => 'Cash on Delivery',
                'description' => 'Pay in cash when your order arrives.',
            ];
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public function allowedChoices(): array
    {
        return array_keys($this->options());
    }

    private function flag(string $key, bool $default = false): bool
    {
        $value = $this->settings->get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
