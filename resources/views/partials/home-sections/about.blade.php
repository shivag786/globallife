<div id="about" class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-12 items-center">
    @if ($section->image_path)
        <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}" class="reveal rounded-2xl w-full object-cover premium-shadow">
    @endif
    <div class="reveal" style="transition-delay: 0.1s">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-5">{{ $section->title }}</h2>
        @if ($section->content)
            <div class="editor-content">{!! $section->content !!}</div>
        @endif
    </div>
</div>
