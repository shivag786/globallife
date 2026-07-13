<?php

namespace App\Support;

class BusinessModules
{
    /**
     * Public-page sections a business owner can turn on/off, keyed by value with an admin-facing label.
     * Adding a new module later is a one-line addition here, not a migration.
     *
     * @var array<string, string>
     */
    public const SECTIONS = [
        'banner' => 'Homepage Banner',
        'about' => 'About Business',
        'services' => 'Services',
        'products' => 'Products',
        'gallery' => 'Gallery',
        'videos' => 'YouTube Videos',
        'reviews' => 'Reviews',
        'contact' => 'Contact Details',
        'faqs' => 'FAQs',
        'social_media' => 'Social Media',
    ];

    /**
     * Floating CTA buttons, individually toggleable.
     *
     * @var array<string, string>
     */
    public const FLOATING_BUTTONS = [
        'float_whatsapp' => 'WhatsApp',
        'float_call' => 'Phone Call',
        'float_direction' => 'Get Direction',
        'float_email' => 'Email',
    ];

    /**
     * All toggleable keys (sections + floating buttons), all defaulting to visible.
     *
     * @return array<string, bool>
     */
    public static function defaults(): array
    {
        $keys = [...array_keys(self::SECTIONS), ...array_keys(self::FLOATING_BUTTONS)];

        return array_fill_keys($keys, true);
    }
}
