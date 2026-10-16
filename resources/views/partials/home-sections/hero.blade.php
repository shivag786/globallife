@php
    $heroSettings = app(\App\Services\SettingsService::class)->all();
@endphp
<div class="position-relative overflow-hidden" style="background: linear-gradient(to bottom, color-mix(in srgb, var(--color-brand-100) 60%, transparent), var(--color-cream), var(--color-cream))">
    <div class="position-absolute top-n24 end-n24 w-96 h-96 rounded-pill animate-float" style="background-color: color-mix(in srgb, var(--color-brand-200) 30%, transparent)" aria-hidden="true"></div>
    <div class="position-absolute bottom-n24 start-n24 w-96 h-96 rounded-pill animate-float" style="animation-delay: -2s; background-color: color-mix(in srgb, var(--color-gold-400) 20%, transparent)" aria-hidden="true"></div>
    <canvas id="hero-3d-canvas" class="position-absolute top-0 start-0 w-100 h-100 z-n1 opacity-75" aria-hidden="true"></canvas>

    <div class="position-relative mw-5xl mx-auto px-6 py-24 py-md-28 text-center">
        @if (! empty($heroSettings['site_tagline']))
            <span class="d-inline-flex align-items-center gap-2 fs-xs fw-semibold text-uppercase tracking-widest text-brand bg-white bg-opacity-75 border border-brand-subtle px-4 py-2 rounded-pill mb-6 animate-fade-in-up">
                <span class="w-1.5 h-1.5 rounded-pill bg-gold"></span>{{ $heroSettings['site_tagline'] }}
            </span>
        @endif

        @if ($section->image_path)
            <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}"
                 class="mx-auto mb-10 rounded-4 premium-shadow mh-96 object-fit-cover animate-fade-in-up">
        @endif

        <h1 class="font-display fs-4xl fs-5xl fw-bold text-brand-emphasis mb-5 lh-1 animate-fade-in-up">{{ $section->title }}</h1>

        @if ($section->subtitle)
            <p class="text-body-secondary fs-lg fs-xl mw-2xl mx-auto mb-9 lh-lg animate-fade-in-up" style="animation-delay: 0.1s">{{ $section->subtitle }}</p>
        @endif

        <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 animate-fade-in-up" style="animation-delay: 0.2s">
            @if ($section->cta_label && $section->cta_url)
                <a href="{{ $section->cta_url }}"
                   class="d-inline-flex align-items-center gap-2 text-white px-8 py-3 rounded-pill fw-medium premium-shadow" style="background: linear-gradient(to bottom right, var(--color-brand-600), var(--color-brand-800))">
                    {{ $section->cta_label }}
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            @endif
            <a href="{{ route('contact') }}"
               class="d-inline-flex align-items-center gap-2 bg-body text-brand-emphasis border border-brand-subtle px-8 py-3 rounded-pill fw-medium">
                Talk to Us
            </a>
        </div>

        @if (! empty($heroSettings['hero_rating']))
            <div class="mt-8 d-inline-flex align-items-center gap-2 animate-fade-in-up" style="animation-delay: 0.3s">
                <div class="d-flex gap-1">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-icon name="star" :filled="true" class="w-4 h-4 text-gold" />
                    @endfor
                </div>
                <span class="fs-sm text-body-secondary">
                    <strong class="text-brand-emphasis">{{ $heroSettings['hero_rating'] }}</strong>
                    @if (! empty($heroSettings['hero_rating_count']))
                        &middot; {{ $heroSettings['hero_rating_count'] }} happy customers
                    @endif
                </span>
            </div>
        @endif

        @if ($section->items)
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-10">
                @foreach ($section->items as $index => $badge)
                    <span class="reveal d-inline-flex align-items-center gap-2 fs-xs fw-semibold text-brand bg-body border border-brand-subtle px-4 py-2 rounded-pill premium-shadow"
                          style="transition-delay: {{ $index * 0.08 }}s">
                        <x-icon name="check-circle" class="w-4 h-4 text-brand" /> {{ $badge['title'] ?? '' }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>
