@php $settings = app(\App\Services\SettingsService::class)->all(); @endphp
<x-layouts.public title="Contact Us" :metaDescription="'Get in touch with Global Life — products, VIP membership, or the business opportunity.'">
    <div class="mw-5xl mx-auto px-6 py-16 d-grid grid-cols-md-2 gap-12">
        <div class="reveal">
            <h1 class="font-display fs-4xl fw-bold text-brand-emphasis mb-4">Get in Touch</h1>
            <p class="text-secondary mb-8">Talk to us today. No pressure, just answers.</p>

            <div class="d-flex flex-column gap-4 fs-sm">
                @if ($settings['contact_address'] ?? null)
                    <div class="d-flex align-items-start gap-3">
                        <x-icon name="map-pin" class="w-5 h-5 text-brand flex-shrink-0 mt-1" />
                        <span>{{ $settings['contact_address'] }}</span>
                    </div>
                @endif
                @if ($settings['contact_email'] ?? null)
                    <div class="d-flex align-items-start gap-3">
                        <x-icon name="envelope" class="w-5 h-5 text-brand flex-shrink-0 mt-1" />
                        <a href="mailto:{{ $settings['contact_email'] }}" class="">{{ $settings['contact_email'] }}</a>
                    </div>
                @endif
                @if ($settings['contact_whatsapp'] ?? null)
                    <div class="d-flex align-items-start gap-3">
                        <x-icon name="phone" class="w-5 h-5 text-brand flex-shrink-0 mt-1" />
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}" class="">{{ $settings['contact_whatsapp'] }}</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="reveal bg-body border rounded-4 p-8 premium-shadow" style="transition-delay: 0.1s">
            @include('partials.enquiry-form', ['source' => 'contact_page'])
        </div>
    </div>
</x-layouts.public>
