<?php

namespace App\Http\Requests\Manager;

use App\Models\VipMicrosite;
use App\Rules\CityWithinCommissionPartner;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreVipMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('commission_partner');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedCityIds = $this->user()->cities()->pluck('cities.id')->all();

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'city_id' => ['required', 'integer', 'exists:cities,id', new CityWithinCommissionPartner($allowedCityIds)],
            'vip_plan_id' => ['required', 'integer', 'exists:vip_plans,id'],
            'business_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('business_name') || ! $this->filled('city_id')) {
                return;
            }

            $slug = Str::slug($this->input('business_name'));
            $exists = VipMicrosite::where('city_id', $this->input('city_id'))
                ->where('business_slug', $slug)
                ->exists();

            if ($exists) {
                $validator->errors()->add('business_name', 'A business with this name already exists in that city.');
            }
        });
    }
}
