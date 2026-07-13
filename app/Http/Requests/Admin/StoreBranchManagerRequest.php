<?php

namespace App\Http\Requests\Admin;

use App\Rules\PercentageWithinCap;
use Illuminate\Foundation\Http\FormRequest;

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
            'mobile' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => ['integer', 'exists:cities,id'],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap(100)],
        ];
    }
}
