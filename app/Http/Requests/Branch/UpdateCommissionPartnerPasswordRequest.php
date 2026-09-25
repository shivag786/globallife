<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionPartnerPasswordRequest extends FormRequest
{
    /**
     * A Branch Manager may only reset a password for a partner they created.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('branch_manager')
            && $this->route('commissionPartner')->created_by === $this->user()->id;
    }

    /**
     * `confirmed` pairs this with `password_confirmation`: setting someone else's
     * password gives no login attempt to catch a typo, so it is entered twice.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.confirmed' => 'The two passwords do not match.',
            'password.min' => 'The password must be at least 8 characters.',
        ];
    }
}
