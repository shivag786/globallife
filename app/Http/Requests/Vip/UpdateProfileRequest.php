<?php

namespace App\Http\Requests\Vip;

use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('vip_member');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Exactly 10 digits after normalisation, and no two accounts may
            // share one — `users.mobile` is written here and from the VIP
            // profile, so both have to agree or the rule means nothing.
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')->ignore($this->user()->id)],

            'owner_name' => ['nullable', 'string', 'max:150'],
            'business_category' => ['nullable', 'string', 'max:100'],
            'business_sub_category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'establishment_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'gst_no' => ['nullable', 'string', 'max:20'],
            'pan_no' => ['nullable', 'string', 'max:20'],
            'cin_no' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_banner' => ['nullable', 'image', 'max:4096'],

            'business_email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'alternate_number' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:255'],

            'address' => ['nullable', 'string', 'max:255'],
            'google_map_url' => ['nullable', 'url', 'max:500'],
            'business_hours' => ['nullable', 'array'],
            'business_hours.*.open' => ['nullable', 'string', 'max:10'],
            'business_hours.*.close' => ['nullable', 'string', 'max:10'],
            'business_hours.*.closed' => ['nullable', 'boolean'],

            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:255'],
            'pinterest_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * Normalise the mobile before any rule sees it, so "+91 98765 43210" and
     * "09876543210" both validate and store as the same 10 digits.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => MobileNumber::normalise($this->input('mobile'))]);
        }
    }
}
