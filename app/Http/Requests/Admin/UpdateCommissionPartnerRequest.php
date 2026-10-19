<?php

namespace App\Http\Requests\Admin;

use App\Rules\PercentageWithinCap;
use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Super Admin editing a Commission Partner directly.
 */
class UpdateCommissionPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $partner = $this->route('commissionPartner');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($partner->id)],
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')->ignore($partner->id)],
            // Same ceiling the Branch Manager works under: a partner's share
            // comes out of their Branch Manager's percentage, so it cannot
            // exceed it without the company paying the difference.
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap($this->commissionCap())],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * The partner's Branch Manager's own percentage, or 100 if they have none.
     */
    public function commissionCap(): float
    {
        $branchManager = $this->route('commissionPartner')->creator;

        return (float) ($branchManager?->commission_percentage ?? 100);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => MobileNumber::normalise($this->input('mobile'))]);
        }
    }
}
