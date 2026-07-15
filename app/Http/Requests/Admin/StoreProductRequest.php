<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ParsesProductFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    use ParsesProductFields;

    public function authorize(): bool
    {
        return $this->user()->can('products.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')],
            'tags' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'mrp' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'gte:price'],
            'offer_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'short_description' => ['required', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string'],
            'usage_instructions' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
            'remove_gallery' => ['nullable', 'boolean'],
            'specs' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['slug'] = Str::slug($data['name']);
        $data['is_featured'] = $this->boolean('is_featured');
        $data['tags'] = $this->parseTags($data['tags'] ?? null);
        $data['specs'] = $this->parseSpecs($data['specs'] ?? null);

        // Gallery files + the reset flag are handled in the controller, not mass-assigned.
        unset($data['gallery'], $data['remove_gallery']);

        return $data;
    }
}
