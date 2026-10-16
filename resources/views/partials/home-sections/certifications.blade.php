@php
    $certIcons = ['shield-check', 'beaker', 'leaf', 'truck', 'check-circle', 'star'];
@endphp
<section class="bg-body border-top border-bottom">
    <div class="mw-6xl mx-auto px-6 py-12">
        @if ($section->title)
            <p class="text-center fs-xs fw-semibold text-uppercase tracking-widest text-secondary mb-9 reveal">{{ $section->title }}</p>
        @endif
        <div class="d-flex flex-wrap justify-content-center column-gap-10 row-gap-8">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal d-flex flex-column align-items-center text-center gap-2 w-32" style="transition-delay: {{ ($index % 6) * 0.08 }}s">
                    <div class="w-14 h-14 rounded-4 border border-brand-subtle d-flex align-items-center justify-content-center" style="background: linear-gradient(to bottom right, var(--color-brand-50), color-mix(in srgb, var(--color-gold-400) 10%, transparent))">
                        <x-icon name="{{ $certIcons[$index % count($certIcons)] }}" class="w-7 h-7 text-brand" />
                    </div>
                    <p class="fs-sm fw-semibold text-brand-emphasis lh-sm">{{ $item['title'] ?? '' }}</p>
                    @if ($item['description'] ?? null)
                        <p class="fs-xs text-secondary lh-sm">{{ $item['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
