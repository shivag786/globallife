@php
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $hours = $microsite->business_hours ?? [];
@endphp
<section id="contact" class="msite-section">
    <div class="msite-container">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
            <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Get In Touch</p>
            <h2 class="msite-heading text-3xl md:text-4xl">Contact Details</h2>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="msite-card p-8 reveal" data-reveal="left">
                <div class="space-y-4 text-sm mb-8">
                    @if ($microsite->address)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center flex-shrink-0">
                                <x-icon name="map-pin" class="w-4 h-4 text-brand-600" />
                            </div>
                            <span class="pt-2">{{ $microsite->address }}</span>
                        </div>
                    @endif
                    @if ($microsite->phone_number)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center flex-shrink-0">
                                <x-icon name="phone" class="w-4 h-4 text-brand-600" />
                            </div>
                            <a href="{{ route('microsite.click', [$microsite, 'call']) }}" class="pt-2 hover:text-brand-700">{{ $microsite->phone_number }}</a>
                        </div>
                    @endif
                    @if ($microsite->business_email)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center flex-shrink-0">
                                <x-icon name="envelope" class="w-4 h-4 text-brand-600" />
                            </div>
                            <a href="mailto:{{ $microsite->business_email }}" class="pt-2 hover:text-brand-700">{{ $microsite->business_email }}</a>
                        </div>
                    @endif
                    @if ($microsite->website_url)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center flex-shrink-0">
                                <x-icon name="share" class="w-4 h-4 text-brand-600" />
                            </div>
                            <a href="{{ route('microsite.click', [$microsite, 'website']) }}" target="_blank" class="pt-2 hover:text-brand-700">{{ $microsite->website_url }}</a>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    @if ($microsite->whatsapp_number)
                        <a href="{{ route('microsite.click', [$microsite, 'whatsapp']) }}" class="msite-btn msite-btn-whatsapp">
                            <x-icon name="chat-bubble" class="w-4 h-4" /> WhatsApp
                        </a>
                    @endif
                    @if ($microsite->google_map_url)
                        <a href="{{ route('microsite.click', [$microsite, 'direction']) }}" class="msite-btn msite-btn-primary">
                            <x-icon name="map-pin" class="w-4 h-4" /> Get Directions
                        </a>
                    @endif
                </div>

                @if ($microsite->google_map_url)
                    <div class="mt-8 rounded-xl overflow-hidden h-56 border border-slate-100">
                        <iframe src="https://maps.google.com/maps?q={{ urlencode($microsite->address ?: $microsite->business_name) }}&output=embed"
                                class="w-full h-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>

            @if (!empty(array_filter($hours)))
                <div class="msite-card p-8 reveal" data-reveal="right">
                    <h3 class="font-heading font-bold text-brand-950 mb-5 text-lg">Business Hours</h3>
                    <div class="text-sm divide-y divide-slate-100">
                        @foreach ($days as $day)
                            <div class="flex justify-between py-2.5">
                                <span class="text-slate-600 capitalize font-medium">{{ $day }}</span>
                                <span class="text-slate-500">
                                    {{ ($hours[$day]['closed'] ?? false) ? 'Closed' : (($hours[$day]['open'] ?? '') . ' – ' . ($hours[$day]['close'] ?? '')) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
