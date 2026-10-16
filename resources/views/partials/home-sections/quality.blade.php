@php
    $qualityIcons = ['beaker', 'leaf', 'shield-check', 'sparkles', 'heart', 'academic-cap'];
    $pillars = $section->items ?? [];
@endphp
<section class="" style="background: linear-gradient(to bottom, var(--color-cream), color-mix(in srgb, var(--color-cream-dark) 30%, transparent))">
    <div class="mw-6xl mx-auto px-6 py-16 py-lg-24">
        <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
            <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">Quality &amp; Transparency</p>
            <h2 class="font-display fs-3xl fs-4xl fw-bold text-brand-emphasis">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary mt-3 fs-lg">{{ $section->subtitle }}</p>
            @endif
        </div>

        @if ($section->image_path)
            <div class="d-grid grid-cols-lg-2 gap-12 align-items-center">
                <div class="reveal" data-reveal="left">
                    <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}"
                         class="w-100 h-80 object-fit-cover rounded-4 premium-shadow">
                </div>
                <div class="d-flex flex-column gap-6">
                    @foreach ($pillars as $index => $pillar)
                        <div class="reveal d-flex gap-4" data-reveal="right" style="transition-delay: {{ ($index % 5) * 0.1 }}s">
                            <div class="flex-shrink-0 w-12 h-12 rounded-3 bg-body border border-brand-subtle d-flex align-items-center justify-content-center premium-shadow">
                                <x-icon name="{{ $qualityIcons[$index % count($qualityIcons)] }}" class="w-6 h-6 text-brand" />
                            </div>
                            <div>
                                <p class="font-display fw-semibold text-brand-emphasis">{{ $pillar['title'] ?? '' }}</p>
                                @if ($pillar['description'] ?? null)
                                    <p class="fs-sm text-secondary mt-1 lh-lg">{{ $pillar['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
                @foreach ($pillars as $index => $pillar)
                    <div class="reveal bg-body rounded-4 p-6 border premium-shadow"
                         style="transition-delay: {{ ($index % 4) * 0.1 }}s">
                        <div class="w-12 h-12 rounded-3 d-flex align-items-center justify-content-center mb-4" style="background: linear-gradient(to bottom right, var(--color-brand-600), var(--color-brand-800))">
                            <x-icon name="{{ $qualityIcons[$index % count($qualityIcons)] }}" class="w-6 h-6 text-white" />
                        </div>
                        <p class="font-display fw-semibold text-brand-emphasis mb-1">{{ $pillar['title'] ?? '' }}</p>
                        @if ($pillar['description'] ?? null)
                            <p class="fs-sm text-secondary lh-lg">{{ $pillar['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
