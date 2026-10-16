<div class="mw-4xl mx-auto px-6 py-16">
    <div class="reveal bg-body rounded-4 p-10 p-md-14 d-flex flex-column flex-md-row align-items-center gap-8 premium-shadow">
        @if ($section->image_path)
            <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}" class="w-28 h-28 rounded-pill object-fit-cover premium-shadow flex-shrink-0">
        @else
            <div class="w-20 h-20 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center flex-shrink-0">
                <x-icon name="chat-bubble" class="w-9 h-9" />
            </div>
        @endif
        <div>
            <div class="font-display fs-xl fs-2xl text-brand-emphasis fst-italic lh-lg [&_p]:m-0">{!! $section->content !!}</div>
            <p class="mt-4 fw-semibold text-brand-emphasis">{{ $section->title }}</p>
            @if ($section->subtitle)
                <p class="fs-sm text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
    </div>
</div>
