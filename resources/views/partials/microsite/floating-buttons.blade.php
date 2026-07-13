<div class="fixed bottom-6 right-6 z-40 flex flex-col gap-3" data-msite-float-group>
    @if ($microsite->isModuleVisible('float_whatsapp') && $microsite->whatsapp_number)
        <a href="{{ route('microsite.click', [$microsite, 'whatsapp']) }}"
           class="msite-float-btn w-14 h-14 rounded-full bg-green-500 text-white flex items-center justify-center shadow-lg hover:bg-green-600 hover:scale-110"
           title="WhatsApp">
            <x-icon name="chat-bubble" class="w-7 h-7" />
        </a>
    @endif
    @if ($microsite->isModuleVisible('float_call') && $microsite->phone_number)
        <a href="{{ route('microsite.click', [$microsite, 'call']) }}"
           class="msite-float-btn w-14 h-14 rounded-full bg-brand-700 text-white flex items-center justify-center shadow-lg hover:bg-brand-800 hover:scale-110"
           title="Call">
            <x-icon name="phone" class="w-6 h-6" />
        </a>
    @endif
    @if ($microsite->isModuleVisible('float_direction') && $microsite->google_map_url)
        <a href="{{ route('microsite.click', [$microsite, 'direction']) }}"
           class="msite-float-btn w-14 h-14 rounded-full bg-gold-500 text-brand-950 flex items-center justify-center shadow-lg hover:bg-gold-400 hover:scale-110"
           title="Directions">
            <x-icon name="map-pin" class="w-6 h-6" />
        </a>
    @endif
    @if ($microsite->isModuleVisible('float_email') && $microsite->business_email)
        <a href="mailto:{{ $microsite->business_email }}"
           class="msite-float-btn w-14 h-14 rounded-full bg-slate-700 text-white flex items-center justify-center shadow-lg hover:bg-slate-800 hover:scale-110"
           title="Email">
            <x-icon name="envelope" class="w-6 h-6" />
        </a>
    @endif
</div>
