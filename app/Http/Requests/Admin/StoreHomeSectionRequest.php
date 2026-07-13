<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ParsesHomeSectionItems;
use App\Models\HomeSection;
use Illuminate\Foundation\Http\FormRequest;

class StoreHomeSectionRequest extends FormRequest
{
    use ParsesHomeSectionItems;

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
            'type' => ['required', 'in:'.implode(',', array_keys(HomeSection::TYPES))],
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'required_if:type,about', 'required_if:type,founder_quote'],
            'image' => ['nullable', 'image', 'max:2048'],
            'cta_label' => ['nullable', 'string', 'max:50', 'required_if:type,cta'],
            'cta_url' => ['nullable', 'string', 'max:255', 'required_if:type,cta'],
            'items' => [
                'nullable', 'string',
                'required_if:type,features', 'required_if:type,stats',
                'required_if:type,business_opportunity', 'required_if:type,process_steps',
                'required_if:type,team',
            ],
            'status' => ['required', 'in:active,inactive'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['items'] = $this->parseItems($data['items'] ?? null, $data['type']);

        return $data;
    }
}
