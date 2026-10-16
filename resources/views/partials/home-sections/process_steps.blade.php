@php $icons = ['heart', 'tag', 'users', 'sparkles']; @endphp
<div class="bg-cream py-16">
    <div class="mw-6xl mx-auto px-6">
        <h2 class="font-display fs-3xl fw-bold text-center text-brand-emphasis mb-12 reveal">{{ $section->title }}</h2>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal bg-body border rounded-4 p-6 premium-shadow position-relative"
                     style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center w-8 h-8 rounded-pill bg-brand text-white fs-sm fw-bold">{{ $index + 1 }}</span>
                        <x-icon :name="$icons[$index % count($icons)]" class="w-5 h-5 text-brand" />
                    </div>
                    <h3 class="font-display fw-semibold text-brand-emphasis mb-2">{{ $item['title'] ?? '' }}</h3>
                    <p class="fs-sm text-secondary">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
