@php $icons = ['academic-cap', 'megaphone', 'users', 'heart']; @endphp
<div id="opportunity" class="mw-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12 reveal">
        <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-3">{{ $section->title }}</h2>
        @if ($section->content)
            <div class="editor-content mw-2xl mx-auto text-start text-sm-center">{!! $section->content !!}</div>
        @endif
    </div>
    <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
        @foreach ($section->items ?? [] as $index => $item)
            <div class="reveal bg-brand-subtle rounded-4 p-6" style="transition-delay: {{ $index * 0.1 }}s">
                <div class="w-11 h-11 rounded-3 bg-body text-brand d-flex align-items-center justify-content-center mb-4 premium-shadow">
                    <x-icon :name="$icons[$index % count($icons)]" class="w-6 h-6" />
                </div>
                <h3 class="font-display fw-semibold text-brand-emphasis mb-2">{{ $item['title'] ?? '' }}</h3>
                <p class="fs-sm text-secondary">{{ $item['description'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
    @if ($section->cta_label && $section->cta_url)
        <div class="text-center mt-10 reveal">
            <a href="{{ $section->cta_url }}" class="btn btn-brand rounded-pill d-inline-flex align-items-center gap-2">
                {{ $section->cta_label }} <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    @endif
</div>
