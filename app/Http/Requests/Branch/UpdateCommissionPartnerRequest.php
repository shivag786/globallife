<?php

namespace App\Http\Requests\Branch;

use App\Rules\PercentageWithinCap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCommissionPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('branch_manager')
            && $this->route('commissionPartner')->created_by === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('commissionPartner')->id],
            // Either an existing city id or a typed name+state; at least one of
            // the two arrays must be non-empty (checked in after() below).
            // Cities outside the branch are allowed: assigning one adds it to the
            // Branch Manager's branch, which is how they take on new territory.
            'cities' => ['nullable', 'array'],
            'cities.*' => ['integer', 'exists:cities,id'],
            'new_cities' => ['nullable', 'array', 'max:25'],
            'new_cities.*.name' => ['required', 'string', 'max:120'],
            'new_cities.*.state' => ['required', 'string', 'max:120'],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap((float) ($this->user()->commission_percentage ?? 0))],
        ];
    }

    /**
     * A partner has to serve at least one city, but it may arrive as either an
     * existing id or a typed name — so the check spans both fields.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $chosen = count($this->input('cities', []));
                $typed = collect($this->input('new_cities', []))
                    ->filter(fn ($row) => filled($row['name'] ?? null))
                    ->count();

                if ($chosen + $typed === 0) {
                    $validator->errors()->add('cities', 'Add at least one city for this Commission Partner.');
                }
            },
        ];
    }
}
