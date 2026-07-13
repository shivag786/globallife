<div class="max-w-4xl mx-auto px-6 py-16">
    <div class="reveal bg-white rounded-2xl p-10 md:p-14 flex flex-col md:flex-row items-center gap-8 premium-shadow">
        @if ($section->image_path)
            <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}" class="w-28 h-28 rounded-full object-cover premium-shadow flex-shrink-0">
        @else
            <div class="w-20 h-20 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center flex-shrink-0">
                <x-icon name="chat-bubble" class="w-9 h-9" />
            </div>
        @endif
        <div>
            <div class="font-display text-xl md:text-2xl text-brand-900 italic leading-relaxed [&_p]:m-0">{!! $section->content !!}</div>
            <p class="mt-4 font-semibold text-brand-800">{{ $section->title }}</p>
            @if ($section->subtitle)
                <p class="text-sm text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
    </div>
</div>
