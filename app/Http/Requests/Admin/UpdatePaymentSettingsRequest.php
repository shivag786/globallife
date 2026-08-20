<?php

namespace App\Http\Requests\Admin;

use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('super_admin');
    }

    protected function prepareForValidation(): void
    {
        // Unchecked checkboxes are absent from the payload — normalise them to 0
        // so a toggle can actually be turned back off.
        $this->merge([
            'razorpay_enabled' => $this->boolean('razorpay_enabled') ? '1' : '0',
            'cod_enabled' => $this->boolean('cod_enabled') ? '1' : '0',
            'payment_test_mode' => $this->boolean('payment_test_mode') ? '1' : '0',
            'razorpay_key_id' => trim((string) $this->input('razorpay_key_id')),
            'razorpay_key_secret' => trim((string) $this->input('razorpay_key_secret')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'razorpay_enabled' => ['required', 'in:0,1'],
            'razorpay_mode' => ['required', 'in:test,live'],
            'razorpay_key_id' => ['nullable', 'string', 'max:100'],
            // Blank means "keep the stored secret" — see the controller.
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
            'razorpay_currency' => ['required', 'string', 'size:3'],
            'cod_enabled' => ['required', 'in:0,1'],
            'payment_test_mode' => ['required', 'in:0,1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('razorpay_enabled') !== '1') {
                return;
            }

            if (! filled($this->input('razorpay_key_id'))) {
                $validator->errors()->add('razorpay_key_id', 'A Key ID is required to enable Razorpay.');
            }

            // Secret may be blank only when one is already stored.
            if (! filled($this->input('razorpay_key_secret')) && ! app(PaymentGatewayService::class)->razorpayKeySecret()) {
                $validator->errors()->add('razorpay_key_secret', 'A Key Secret is required to enable Razorpay.');
            }
        });
    }
}
