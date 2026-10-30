<?php

namespace App\Support;

use App\Models\VipMicrosite;
use Illuminate\Support\Collection;

/**
 * Reads a microsite's `page_layout` JSON: the order of its sections, each
 * section's heading (eyebrow / title / subtitle) and the highlight counters.
 *
 * The JSON is always optional. A page that has never been through the builder
 * has no layout at all and renders exactly as before — default order, default
 * headings — so every read goes through here and falls back to DEFAULTS.
 *
 * The hero banner is pinned first and the enquiry form last; everything in
 * between can be dragged. Owner-built card sections appear as "custom-{id}".
 */
class BusinessPageLayout
{
    /**
     * Built-in sections in their default order, with the heading the public
     * page has always shown and a one-line hint for the builder.
     *
     * @var array<string, array{label: string, eyebrow: string, title: string, subtitle: string, hint: string}>
     */
    public const DEFAULTS = [
        'about' => [
            'label' => 'About Us',
            'eyebrow' => 'About Us',
            'title' => 'About {business}',
            'subtitle' => '',
            'hint' => 'Who you are, since when, and what makes you different.',
        ],
        'stats' => [
            'label' => 'Highlights (numbers)',
            'eyebrow' => 'Why Choose Us',
            'title' => 'Numbers that speak for us',
            'subtitle' => '',
            'hint' => 'Big numbers customers trust: years of service, happy customers, experts on the team.',
        ],
        'shop' => [
            'label' => 'Company Shop',
            'eyebrow' => 'Shop',
            'title' => 'Featured Products',
            'subtitle' => 'Genuine products, delivered to your door.',
            'hint' => 'Company products you resell. Choose which ones show under My Store.',
        ],
        'services' => [
            'label' => 'Services',
            'eyebrow' => 'What We Offer',
            'title' => 'Our Services',
            'subtitle' => '',
            'hint' => 'Each thing you do for customers, with a photo, a short line and the price.',
        ],
        'products' => [
            'label' => 'My Products',
            'eyebrow' => 'Shop',
            'title' => 'Our Products',
            'subtitle' => '',
            'hint' => 'Products you sell and deliver yourself.',
        ],
        'gallery' => [
            'label' => 'Gallery',
            'eyebrow' => 'Take a Look',
            'title' => 'Gallery',
            'subtitle' => '',
            'hint' => 'Photos of your place, team and work.',
        ],
        'videos' => [
            'label' => 'YouTube Videos',
            'eyebrow' => 'Watch',
            'title' => 'Videos',
            'subtitle' => '',
            'hint' => 'YouTube videos about your business.',
        ],
        'reviews' => [
            'label' => 'Reviews',
            'eyebrow' => 'Testimonials',
            'title' => 'What Customers Say',
            'subtitle' => '',
            'hint' => 'Customer reviews you have approved.',
        ],
        'faqs' => [
            'label' => 'FAQs',
            'eyebrow' => 'Questions',
            'title' => 'Frequently Asked Questions',
            'subtitle' => '',
            'hint' => 'Questions customers ask you on the phone, answered once here.',
        ],
        'contact' => [
            'label' => 'Contact Details',
            'eyebrow' => 'Get In Touch',
            'title' => 'Contact Details',
            'subtitle' => '',
            'hint' => 'Address, phone, email and opening hours from your Profile.',
        ],
        'social_media' => [
            'label' => 'Social Media',
            'eyebrow' => '',
            'title' => 'Follow {business}',
            'subtitle' => '',
            'hint' => 'Links to your social pages from your Profile.',
        ],
    ];

    public const MAX_STATS = 8;

    public function __construct(private readonly VipMicrosite $microsite)
    {
    }

    /**
     * @return array<string, mixed>
     */
    private function raw(): array
    {
        return is_array($this->microsite->page_layout) ? $this->microsite->page_layout : [];
    }

    /**
     * Every section key in page order: the saved order first (dropping keys that
     * no longer exist), then any section the saved order does not know about yet
     * — a built-in added in a later release, or a custom section created since.
     *
     * @param  Collection<int, \App\Models\BusinessSection>|null  $customSections
     * @return array<int, string>
     */
    public function order(?Collection $customSections = null): array
    {
        $customSections ??= $this->microsite->customSections;

        $known = [...array_keys(self::DEFAULTS), ...$customSections->map->layoutKey()->all()];

        $saved = array_values(array_filter(
            (array) ($this->raw()['order'] ?? []),
            fn ($key) => is_string($key) && in_array($key, $known, true),
        ));

        $missing = array_values(array_diff($known, $saved));

        // A new custom section slots in just before Gallery by default, rather
        // than at the very bottom under Contact.
        if ($saved === []) {
            $builtIns = array_keys(self::DEFAULTS);
            $customs = array_values(array_diff($missing, $builtIns));
            $at = array_search('gallery', $builtIns, true);
            array_splice($builtIns, $at, 0, $customs);

            return $builtIns;
        }

        return array_values(array_unique([...$saved, ...$missing]));
    }

    /**
     * The heading shown above a built-in section, with the owner's edits laid
     * over the defaults. "{business}" is replaced with the business name.
     *
     * @return array{eyebrow: string, title: string, subtitle: string}
     */
    public function heading(string $key): array
    {
        $saved = (array) ($this->raw()['headings'][$key] ?? []);
        $default = $this->defaultHeading($key);

        $pick = fn (string $field) => array_key_exists($field, $saved) && $saved[$field] !== null
            ? str_replace('{business}', (string) $this->microsite->business_name, (string) $saved[$field])
            : $default[$field];

        return [
            'eyebrow' => $pick('eyebrow'),
            'title' => $pick('title'),
            'subtitle' => $pick('subtitle'),
        ];
    }

    /**
     * The heading a section shows when its owner has not changed it.
     *
     * @return array{eyebrow: string, title: string, subtitle: string}
     */
    public function defaultHeading(string $key): array
    {
        $default = self::DEFAULTS[$key] ?? ['eyebrow' => '', 'title' => '', 'subtitle' => ''];
        $replace = fn (string $text) => str_replace('{business}', (string) $this->microsite->business_name, $text);

        return [
            'eyebrow' => $replace((string) $default['eyebrow']),
            'title' => $replace((string) $default['title']),
            'subtitle' => $replace((string) $default['subtitle']),
        ];
    }

    /**
     * Highlight counters. `$onlyActive` is what the public page wants; the
     * builder needs the switched-off ones too.
     *
     * @return array<int, array{label: string, value: string, suffix: string, is_active: bool}>
     */
    public function stats(bool $onlyActive = false): array
    {
        $stats = array_map(fn ($stat) => [
            'label' => (string) ($stat['label'] ?? ''),
            'value' => (string) ($stat['value'] ?? ''),
            'suffix' => (string) ($stat['suffix'] ?? ''),
            'is_active' => (bool) ($stat['is_active'] ?? true),
        ], array_values((array) ($this->raw()['stats'] ?? [])));

        return $onlyActive
            ? array_values(array_filter($stats, fn ($stat) => $stat['is_active'] && $stat['label'] !== ''))
            : $stats;
    }

    public function hasCustomStats(): bool
    {
        return $this->stats(true) !== [];
    }
}
