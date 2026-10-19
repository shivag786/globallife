<?php

namespace App\Http\Requests\Branch;

use App\Http\Requests\Concerns\ValidatesMemberProfile;
use App\Rules\PercentageWithinCap;
use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommissionPartnerRequest extends FormRequest
{
    use ValidatesMemberProfile;

    public function authorize(): bool
    {
        return $this->user()->hasRole('branch_manager')
            && $this->route('commissionPartner')->created_by === $this->user()->id;
    }

    /**
     * Cities are not set here any more. A Commission Partner picks up a city when
     * they register a VIP Member in one, so the territory follows the work.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $partnerId = $this->route('commissionPartner')->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$partnerId],
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')->ignore($partnerId)],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap((float) ($this->user()->commission_percentage ?? 0))],

            // Personal, address, nominee and KYC -- all optional.
            ...$this->profileRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->profileMessages();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => MobileNumber::normalise($this->input('mobile'))]);
        }

        $this->prepareProfileForValidation();
    }
}
