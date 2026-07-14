@php
    $heroSettings = app(\App\Services\SettingsService::class)->all();
@endphp
<div class="relative bg-gradient-to-b from-brand-100/60 via-cream to-cream overflow-hidden">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-200/30 rounded-full blur-3xl animate-float" aria-hidden="true"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-gold-400/20 rounded-full blur-3xl animate-float" style="animation-delay: -2s" aria-hidden="true"></div>
    <canvas id="hero-3d-canvas" class="absolute inset-0 -z-10 opacity-70" aria-hidden="true"></canvas>

    <div class="relative max-w-5xl mx-auto px-6 py-24 md:py-28 text-center">
        @if (! empty($heroSettings['site_tagline']))
            <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-brand-700 bg-white/70 backdrop-blur border border-brand-100 px-4 py-1.5 rounded-full mb-6 animate-fade-in-up">
                <span class="w-1.5 h-1.5 rounded-full bg-gold-500"></span>{{ $heroSettings['site_tagline'] }}
            </span>
        @endif

        @if ($section->image_path)
            <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->title }}"
                 class="mx-auto mb-10 rounded-2xl premium-shadow max-h-96 object-cover animate-fade-in-up">
        @endif

        <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-brand-900 mb-5 leading-[1.1] animate-fade-in-up">{{ $section->title }}</h1>

        @if ($section->subtitle)
            <p class="text-slate-600 text-lg md:text-xl max-w-2xl mx-auto mb-9 leading-relaxed animate-fade-in-up" style="animation-delay: 0.1s">{{ $section->subtitle }}</p>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-4 animate-fade-in-up" style="animation-delay: 0.2s">
            @if ($section->cta_label && $section->cta_url)
                <a href="{{ $section->cta_url }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-br from-brand-600 to-brand-800 text-white px-8 py-3.5 rounded-full font-medium hover:-translate-y-0.5 hover:brightness-105 transition premium-shadow">
                    {{ $section->cta_label }}
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            @endif
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2 bg-white text-brand-800 border border-brand-200 px-8 py-3.5 rounded-full font-medium hover:border-brand-400 hover:-translate-y-0.5 transition">
                Talk to Us
            </a>
        </div>

        @if (! empty($heroSettings['hero_rating']))
            <div class="mt-8 inline-flex items-center gap-2.5 animate-fade-in-up" style="animation-delay: 0.3s">
                <div class="flex gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-icon name="star" :filled="true" class="w-4 h-4 text-gold-500" />
                    @endfor
                </div>
                <span class="text-sm text-slate-600">
                    <strong class="text-brand-900">{{ $heroSettings['hero_rating'] }}</strong>
                    @if (! empty($heroSettings['hero_rating_count']))
                        &middot; {{ $heroSettings['hero_rating_count'] }} happy customers
                    @endif
                </span>
            </div>
        @endif

        @if ($section->items)
            <div class="flex flex-wrap justify-center gap-3 mt-10">
                @foreach ($section->items as $index => $badge)
                    <span class="reveal inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 bg-white border border-brand-100 px-4 py-2 rounded-full premium-shadow"
                          style="transition-delay: {{ $index * 0.08 }}s">
                        <x-icon name="check-circle" class="w-4 h-4 text-brand-500" /> {{ $badge['title'] ?? '' }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>
