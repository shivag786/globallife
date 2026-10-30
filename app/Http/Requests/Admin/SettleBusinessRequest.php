<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettleBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Same proof rule as a VIP withdrawal: a UTR / transaction number, a
     * screenshot, or both — never neither.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'utr_number' => ['nullable', 'required_without:payment_screenshot', 'string', 'max:120'],
            'payment_screenshot' => ['nullable', 'required_without:utr_number', 'image', 'max:4096'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'utr_number.required_without' => 'Enter the UTR / transaction number or upload a screenshot of the transfer.',
            'payment_screenshot.required_without' => 'Upload a screenshot of the transfer or enter its UTR / transaction number.',
        ];
    }
}
