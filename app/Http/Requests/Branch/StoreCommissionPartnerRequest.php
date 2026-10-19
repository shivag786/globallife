<?php

namespace App\Http\Requests\Branch;

use App\Http\Requests\Concerns\ValidatesMemberProfile;
use App\Rules\PercentageWithinCap;
use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommissionPartnerRequest extends FormRequest
{
    use ValidatesMemberProfile;

    public function authorize(): bool
    {
        return $this->user()->hasRole('branch_manager');
    }

    /**
     * Cities are not set here any more. A Commission Partner picks up a city when
     * they register a VIP Member in one, so the territory follows the work.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // Exactly 10 digits after normalisation, unique across all accounts.
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')],
            'password' => ['required', 'string', 'min:8'],
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
