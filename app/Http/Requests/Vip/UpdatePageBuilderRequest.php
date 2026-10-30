<?php

namespace App\Http\Requests\Vip;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The page builder's whole document. Error keys follow the document's own shape
 * ("sections.3.items.2.name"), which the editor uses to point at the field.
 */
class UpdatePageBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('vip_member') && $this->user()->vipMicrosite !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'max:40'],
            'sections.*.key' => ['required', 'string', 'max:40', 'regex:/^[a-z_]+$|^custom-\d+$|^new-[a-z0-9]+$/'],
            'sections.*.type' => ['required', 'in:about,stats,services,products,faqs,custom,linked'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.visible' => ['boolean'],
            'sections.*.style' => ['nullable', 'in:cards,people'],
            'sections.*.heading.eyebrow' => ['nullable', 'string', 'max:80'],
            'sections.*.heading.title' => ['nullable', 'string', 'max:150'],
            'sections.*.heading.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.*.content.short_description' => ['nullable', 'string', 'max:255'],
            'sections.*.content.description' => ['nullable', 'string', 'max:5000'],

            'sections.*.items' => ['nullable', 'array', 'max:150'],
            'sections.*.items.*.id' => ['nullable', 'integer'],
            'sections.*.items.*.is_active' => ['boolean'],
            // Cards (services, products, custom)
            'sections.*.items.*.name' => ['nullable', 'string', 'max:150'],
            'sections.*.items.*.title' => ['nullable', 'string', 'max:150'],
            'sections.*.items.*.subtitle' => ['nullable', 'string', 'max:150'],
            'sections.*.items.*.category' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.short_description' => ['nullable', 'string', 'max:300'],
            'sections.*.items.*.long_description' => ['nullable', 'string', 'max:5000'],
            'sections.*.items.*.image_path' => ['nullable', 'string', 'max:255'],
            'sections.*.items.*.mrp' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'sections.*.items.*.offer_price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'sections.*.items.*.stock' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'sections.*.items.*.show_book_now' => ['boolean'],
            'sections.*.items.*.button_label' => ['nullable', 'string', 'max:40'],
            'sections.*.items.*.button_url' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/|tel:|mailto:|#)/i'],
            // FAQs
            'sections.*.items.*.question' => ['nullable', 'string', 'max:255'],
            'sections.*.items.*.answer' => ['nullable', 'string', 'max:2000'],
            // Highlights
            'sections.*.items.*.label' => ['nullable', 'string', 'max:60'],
            'sections.*.items.*.value' => ['nullable', 'string', 'max:20'],
            'sections.*.items.*.suffix' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sections.*.items.*.button_url.regex' => 'A button link must start with https://, tel:, mailto: or #.',
            'sections.*.items.*.mrp.numeric' => 'MRP must be a number.',
            'sections.*.items.*.offer_price.numeric' => 'Sale price must be a number.',
            'sections.*.items.*.stock.integer' => 'Stock must be a whole number.',
        ];
    }

    /**
     * The per-type "this field is required" checks, written out so each message
     * names the section and card the owner has to look at.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ((array) $this->input('sections', []) as $s => $section) {
                $type = $section['type'] ?? null;
                $label = trim((string) ($section['heading']['title'] ?? '')) ?: ucfirst((string) ($section['key'] ?? 'section'));

                if ($type === 'custom' && trim((string) ($section['heading']['title'] ?? '')) === '') {
                    $validator->errors()->add("sections.$s.heading.title", 'Every section needs a title.');
                }

                foreach ((array) ($section['items'] ?? []) as $i => $item) {
                    $n = $i + 1;
                    $path = "sections.$s.items.$i";

                    $required = match ($type) {
                        'services', 'products' => ['name' => 'a name'],
                        'custom' => ['title' => 'a title'],
                        'faqs' => ['question' => 'a question', 'answer' => 'an answer'],
                        'stats' => ['label' => 'a label', 'value' => 'a number'],
                        default => [],
                    };

                    foreach ($required as $field => $what) {
                        if (trim((string) ($item[$field] ?? '')) === '') {
                            $validator->errors()->add("$path.$field", "$label: card $n needs $what.");
                        }
                    }

                    $mrp = $item['mrp'] ?? null;
                    $offer = $item['offer_price'] ?? null;
                    if (is_numeric($mrp) && is_numeric($offer) && (float) $offer > (float) $mrp) {
                        $validator->errors()->add("$path.offer_price", "$label: card $n has a sale price above its MRP.");
                    }
                }
            }
        });
    }
}
