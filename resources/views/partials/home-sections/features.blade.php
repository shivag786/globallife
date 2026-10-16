@php $icons = ['shield-check', 'heart', 'rupee', 'truck']; @endphp
<div class="bg-cream py-16">
    <div class="mw-6xl mx-auto px-6">
        <h2 class="font-display fs-3xl fw-bold text-center text-brand-emphasis mb-12 reveal">{{ $section->title }}</h2>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal bg-body border rounded-4 p-6 premium-shadow"
                     style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="w-11 h-11 rounded-3 bg-brand-subtle text-brand d-flex align-items-center justify-content-center mb-4">
                        <x-icon :name="$icons[$index % count($icons)]" class="w-6 h-6" />
                    </div>
                    <h3 class="font-display fw-semibold text-brand-emphasis mb-2">{{ $item['title'] ?? '' }}</h3>
                    <p class="fs-sm text-secondary">{{ $item['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
