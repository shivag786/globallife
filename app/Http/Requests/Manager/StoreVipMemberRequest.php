<?php

namespace App\Http\Requests\Manager;

use App\Models\City;
use App\Models\VipMicrosite;
use App\Services\CityDirectoryService;
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
     * A typed city that already exists is resolved to its id here, so the rest of
     * the request — including the per-city business-name check below — treats it
     * exactly like one picked from the list. A genuinely new city is left for the
     * service to create, rather than creating a row during validation that a
     * later failure would strand.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('city_id') || ! $this->filled('new_city.name')) {
            return;
        }

        $existing = City::whereRaw('LOWER(name) = ?', [Str::lower(Str::squish($this->input('new_city.name')))])
            ->whereRaw('LOWER(state) = ?', [Str::lower(Str::squish((string) $this->input('new_city.state')))])
            ->first();

        if ($existing) {
            $this->merge(['city_id' => $existing->id]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            // Either an existing city, or a typed name + state for a new one.
            // No branch restriction: registering a member is how a Commission
            // Partner takes on a city.
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'new_city.name' => ['required_without:city_id', 'nullable', 'string', 'max:120'],
            'new_city.state' => ['required_with:new_city.name', 'nullable', 'string', 'max:120'],
            'vip_plan_id' => ['required', 'integer', 'exists:vip_plans,id'],
            'business_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_city.name.required_without' => 'Choose a city, or pick "Other" and type one.',
            'new_city.state.required_with' => 'Choose the state for that city.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Only an existing city can already hold a business of this name; a
            // brand-new city has no microsites, so there is nothing to clash with.
            if (! $this->filled('business_name') || ! $this->filled('city_id')) {
                return;
            }

            $exists = VipMicrosite::where('city_id', $this->input('city_id'))
                ->where('business_slug', Str::slug($this->input('business_name')))
                ->exists();

            if ($exists) {
                $validator->errors()->add('business_name', 'A business with this name already exists in that city.');
            }
        });
    }

    /**
     * The city this member belongs to, creating it when it is genuinely new.
     */
    public function resolveCity(CityDirectoryService $cities): City
    {
        if ($this->filled('city_id')) {
            return City::findOrFail($this->integer('city_id'));
        }

        return $cities->resolveOrCreate(
            (string) $this->input('new_city.name'),
            (string) $this->input('new_city.state'),
        );
    }
}
