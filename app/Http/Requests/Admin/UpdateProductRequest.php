<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ParsesProductFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateProductRequest extends FormRequest
{
    use ParsesProductFields;

    public function authorize(): bool
    {
        return $this->user()->can('products.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:50'],
            'short_description' => ['required', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'max:2048'],
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

        return $data;
    }
}
