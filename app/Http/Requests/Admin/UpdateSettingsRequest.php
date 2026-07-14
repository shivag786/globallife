<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            // General
            'site_title' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],

            // SEO
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'robots' => ['nullable', 'in:index,noindex'],

            // Social & OG
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'twitter_card' => ['nullable', 'in:summary,summary_large_image'],
            'twitter_site' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
            'social_linkedin' => ['nullable', 'string', 'max:255'],

            // Analytics
            'ga_measurement_id' => ['nullable', 'string', 'max:50', 'regex:/^G-[A-Z0-9]+$/i'],
            'gtm_id' => ['nullable', 'string', 'max:50', 'regex:/^GTM-[A-Z0-9]+$/i'],

            // Homepage & Announcement
            'announcement_text' => ['nullable', 'string', 'max:160'],
            'announcement_link' => ['nullable', 'url', 'max:255'],
            'hero_rating' => ['nullable', 'string', 'max:10'],
            'hero_rating_count' => ['nullable', 'string', 'max:30'],

            // Contact
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'contact_address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
