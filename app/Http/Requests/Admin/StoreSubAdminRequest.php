<?php

namespace App\Http\Requests\Admin;

use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only Super Admin creates the layer directly beneath it; a Sub Admin
        // cannot mint more Sub Admins.
        return $this->user()->hasRole('super_admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => MobileNumber::normalise($this->input('mobile'))]);
        }
    }
}
