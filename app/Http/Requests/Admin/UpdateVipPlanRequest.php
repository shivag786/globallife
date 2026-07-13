<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateVipPlanRequest extends FormRequest
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
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'joining_price' => ['required', 'numeric', 'min:0'],
            'renewal_price' => ['required', 'numeric', 'min:0'],
            'features' => ['nullable', 'string'],
            'highlight_features' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'upgrade_priority' => ['required', 'integer', 'min:0'],
            'display_order' => ['required', 'integer', 'min:0'],
            'microsite_limit' => ['required', 'integer', 'min:0'],
            'landing_page_limit' => ['required', 'integer', 'min:0'],
            'blog_limit' => ['required', 'integer', 'min:0'],
            'analytics_limit_days' => ['required', 'integer', 'min:0'],
            'storage_limit_mb' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['slug'] = Str::slug($data['name']);
        $data['features'] = $this->linesToArray($data['features'] ?? null);
        $data['highlight_features'] = $this->linesToArray($data['highlight_features'] ?? null);

        return $data;
    }

    /**
     * @return list<string>
     */
    private function linesToArray(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return collect(explode("\n", $text))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
