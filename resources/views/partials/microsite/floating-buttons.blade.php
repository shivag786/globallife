<div class="position-fixed bottom-6 end-6 z-floating d-flex flex-column gap-3" data-msite-float-group>
    @if ($microsite->isModuleVisible('float_whatsapp') && $microsite->whatsapp_number)
        <a href="{{ route('microsite.click', [$microsite, 'whatsapp']) }}"
           class="msite-float-btn w-14 h-14 rounded-pill bg-success text-white d-flex align-items-center justify-content-center shadow"
           title="WhatsApp">
            <x-icon name="chat-bubble" class="w-7 h-7" />
        </a>
    @endif
    @if ($microsite->isModuleVisible('float_call') && $microsite->phone_number)
        <a href="{{ route('microsite.click', [$microsite, 'call']) }}"
           class="msite-float-btn w-14 h-14 rounded-pill bg-brand text-white d-flex align-items-center justify-content-center shadow"
           title="Call">
            <x-icon name="phone" class="w-6 h-6" />
        </a>
    @endif
    @if ($microsite->isModuleVisible('float_direction') && $microsite->google_map_url)
        <a href="{{ route('microsite.click', [$microsite, 'direction']) }}"
           class="msite-float-btn w-14 h-14 rounded-pill bg-gold text-brand-emphasis d-flex align-items-center justify-content-center shadow"
           title="Directions">
            <x-icon name="map-pin" class="w-6 h-6" />
        </a>
    @endif
    @if ($microsite->isModuleVisible('float_email') && $microsite->business_email)
        <a href="mailto:{{ $microsite->business_email }}"
           class="msite-float-btn w-14 h-14 rounded-pill bg-dark text-white d-flex align-items-center justify-content-center shadow"
           title="Email">
            <x-icon name="envelope" class="w-6 h-6" />
        </a>
    @endif
</div>
