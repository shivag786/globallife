<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Services\PaymentGatewayService;
use App\Services\RazorpayService;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Payment gateway configuration (super_admin only). The stored secret is never
 * sent back to the browser — a blank secret field means "leave it as it is".
 */
class PaymentSettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly PaymentGatewayService $gateway,
    ) {}

    public function edit(): View
    {
        return view('admin.settings.payment', [
            'gateway' => $this->gateway,
            'hasSecret' => filled($this->gateway->razorpayKeySecret()),
        ]);
    }

    public function update(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->settings->set(PaymentGatewayService::KEY_ENABLED, $data['razorpay_enabled']);
        $this->settings->set(PaymentGatewayService::KEY_MODE, $data['razorpay_mode']);
        $this->settings->set(PaymentGatewayService::KEY_ID, $data['razorpay_key_id'] ?: null);
        $this->settings->set(PaymentGatewayService::KEY_CURRENCY, strtoupper($data['razorpay_currency']));
        $this->settings->set(PaymentGatewayService::KEY_COD, $data['cod_enabled']);
        $this->settings->set(PaymentGatewayService::KEY_TEST_MODE, $data['payment_test_mode']);

        if (filled($data['razorpay_key_secret'] ?? null)) {
            $this->gateway->setRazorpayKeySecret($data['razorpay_key_secret']);
        }

        return redirect()->route('admin.settings.payment.edit')
            ->with('status', 'Payment settings updated successfully.');
    }

    /**
     * Verify the saved credentials against the live Razorpay API.
     */
    public function test(RazorpayService $razorpay): RedirectResponse
    {
        $result = $razorpay->testConnection();

        return redirect()->route('admin.settings.payment.edit')
            ->with($result['ok'] ? 'status' : 'error', $result['message']);
    }
}
