<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', 'in:contact_page,homepage,chatbot,microsite'],
            'interested_plan_id' => ['nullable', 'integer', 'exists:vip_plans,id'],
            'vip_microsite_id' => ['nullable', 'integer', 'exists:vip_microsites,id'],
            // Honeypot: real visitors never fill this hidden field; bots usually do.
            'website' => ['prohibited'],
        ];
    }
}
