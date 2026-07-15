<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionRulesRequest extends FormRequest
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
            'rules' => ['nullable', 'array'],
            'rules.*.type' => ['nullable', 'in:percent,fixed'],
            'rules.*.value' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ((array) $this->input('rules', []) as $role => $row) {
                $type = $row['type'] ?? 'percent';
                $value = $row['value'] ?? null;

                if ($type === 'percent' && $value !== null && $value !== '' && (float) $value > 100) {
                    $validator->errors()->add("rules.{$role}.value", 'A percentage cannot exceed 100.');
                }
            }
        });
    }
}
