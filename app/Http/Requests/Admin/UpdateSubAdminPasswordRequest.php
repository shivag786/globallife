<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubAdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    /**
     * `confirmed` pairs this with `password_confirmation`: setting someone else's
     * password gets no login attempt to catch a typo, so it is entered twice.
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
