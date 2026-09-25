<?php

namespace App\Http\Requests\Admin;

use App\Rules\PercentageWithinCap;
use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchManagerRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:120'],
            // Exactly 10 digits after normalisation, and no two accounts may
            // share one — `users.mobile` is written here and from the VIP
            // profile, so both have to agree or the rule means nothing.
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            // Optional: a Branch Manager may be created before their territory is
            // decided, and they pick up cities anyway when assigning a partner.
            'cities' => ['nullable', 'array'],
            'cities.*' => ['integer', 'exists:cities,id'],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap(100)],
        ];
    }

    /**
     * Normalise the mobile before any rule sees it, so "+91 98765 43210" and
     * "09876543210" both validate and store as the same 10 digits.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => MobileNumber::normalise($this->input('mobile'))]);
        }
    }
}
