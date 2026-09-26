<?php

namespace App\Http\Requests\Vip;

use App\Models\BusinessService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('vip_member')
            && $this->route('service')->vip_microsite_id === $this->user()->vipMicrosite->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'string', 'max:150'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:255'],
            // Discount and strike-through are derived from these two, never typed
            // — see BusinessService::discountPercentage(). Either may stand alone,
            // so the sale price is only compared against an MRP that was given.
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'offer_price' => array_values(array_filter([
                'nullable', 'numeric', 'min:0',
                $this->filled('mrp') ? 'lte:mrp' : null,
            ])),
            'show_pricing' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_book_now' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'offer_price.lte' => 'The sale price has to be lower than the MRP — otherwise there is no discount to show.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $micrositeId = $this->user()->vipMicrosite->id;
            $exists = BusinessService::where('vip_microsite_id', $micrositeId)
                ->where('slug', $this->input('slug'))
                ->where('id', '!=', $this->route('service')->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'You already have a service with this name.');
            }
        });
    }
}
