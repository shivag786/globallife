<div id="about" class="mw-6xl mx-auto px-6 py-16 d-grid grid-cols-md-2 gap-12 align-items-center">
    @if ($section->image_path)
        <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}" class="reveal rounded-4 w-100 object-fit-cover premium-shadow">
    @endif
    <div class="reveal" style="transition-delay: 0.1s">
        <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-5">{{ $section->title }}</h2>
        @if ($section->content)
            <div class="editor-content">{!! $section->content !!}</div>
        @endif
    </div>
</div>
