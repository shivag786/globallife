<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MarkWithdrawalPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    /**
     * Either a UTR number or a screenshot must be supplied — that is the proof
     * the VIP member is shown, so a payment cannot be recorded without one.
     * Both together is fine.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'utr_number' => ['nullable', 'required_without:payment_screenshot', 'string', 'max:120'],
            'payment_screenshot' => ['nullable', 'required_without:utr_number', 'image', 'max:4096'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'utr_number.required_without' => 'Enter the UTR number or upload a payment screenshot.',
            'payment_screenshot.required_without' => 'Upload a payment screenshot or enter the UTR number.',
            'payment_screenshot.max' => 'The screenshot must be 4 MB or smaller.',
        ];
    }
}
