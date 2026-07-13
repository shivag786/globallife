@php $settings = app(\App\Services\SettingsService::class)->all(); @endphp
<x-layouts.public title="Contact Us" :metaDescription="'Get in touch with Global Life — products, VIP membership, or the business opportunity.'">
    <div class="max-w-5xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-12">
        <div class="reveal">
            <h1 class="font-display text-4xl font-bold text-brand-900 mb-4">Get in Touch</h1>
            <p class="text-slate-500 mb-8">Talk to us today. No pressure, just answers.</p>

            <div class="space-y-4 text-sm">
                @if ($settings['contact_address'] ?? null)
                    <div class="flex items-start gap-3">
                        <x-icon name="map-pin" class="w-5 h-5 text-brand-500 flex-shrink-0 mt-0.5" />
                        <span>{{ $settings['contact_address'] }}</span>
                    </div>
                @endif
                @if ($settings['contact_email'] ?? null)
                    <div class="flex items-start gap-3">
                        <x-icon name="envelope" class="w-5 h-5 text-brand-500 flex-shrink-0 mt-0.5" />
                        <a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-brand-700">{{ $settings['contact_email'] }}</a>
                    </div>
                @endif
                @if ($settings['contact_whatsapp'] ?? null)
                    <div class="flex items-start gap-3">
                        <x-icon name="phone" class="w-5 h-5 text-brand-500 flex-shrink-0 mt-0.5" />
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}" class="hover:text-brand-700">{{ $settings['contact_whatsapp'] }}</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="reveal bg-white border border-slate-100 rounded-2xl p-8 premium-shadow" style="transition-delay: 0.1s">
            @include('partials.enquiry-form', ['source' => 'contact_page'])
        </div>
    </div>
</x-layouts.public>
