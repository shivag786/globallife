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
            'source' => ['required', 'in:contact_page,homepage,chatbot,microsite,product'],
            'interested_plan_id' => ['nullable', 'integer', 'exists:vip_plans,id'],
            'vip_microsite_id' => ['nullable', 'integer', 'exists:vip_microsites,id'],
            // Which product page the enquiry was sent from. Only meaningful for
            // product enquiries; the record of the product, not the message.
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            // Form bookkeeping, never stored: which form on which page sent this
            // (so a failed submission refills only that form), and where to send
            // the visitor back to.
            'enquiry_context' => ['nullable', 'string', 'max:100'],
            'return_to' => ['nullable', 'string', 'max:2000'],
            // Honeypot: real visitors never fill this hidden field; bots usually do.
            'website' => ['prohibited'],
        ];
    }

    /**
     * Where to return the visitor: the page the form was on.
     *
     * The default "back" is whatever page the session last recorded, which is
     * not necessarily the one submitted from -- another tab, or (while uploads
     * are served through Laravel) the last image requested. A failed product
     * enquiry then landed on a different product with this one's message.
     *
     * Only a path on this site is accepted, so the field cannot be used to bounce
     * visitors to another domain.
     */
    public function returnUrl(): ?string
    {
        $target = $this->input('return_to');

        if (! is_string($target) || $target === '') {
            return null;
        }

        $parts = parse_url($target);

        if ($parts === false) {
            return null;
        }

        // Absolute URLs must point at this host; protocol-relative and other
        // schemes are refused outright.
        if (isset($parts['scheme']) || isset($parts['host'])) {
            if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)
                || ($parts['host'] ?? null) !== $this->getHost()) {
                return null;
            }
        }

        $path = $parts['path'] ?? '/';

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return url($path.(isset($parts['query']) ? '?'.$parts['query'] : ''));
    }

    protected function getRedirectUrl(): string
    {
        $return = $this->returnUrl();

        return $return ? $return.'#enquire' : parent::getRedirectUrl();
    }
}
