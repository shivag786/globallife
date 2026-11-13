@php
    $whatsapp = app(\App\Services\SettingsService::class)->all()['contact_whatsapp'] ?? null;
    $talkUrl = $whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $whatsapp) : route('contact');
    $stats = array_values(array_filter($section->items ?? [], fn ($item) => ($item['value'] ?? '') !== ''));
@endphp
@once
    <style>
        .wwa-photo-wrap { position: relative; padding: 0 0 4.5rem; }
        .wwa-rings { position: absolute; top: -1.75rem; left: -1.75rem; width: 6.5rem; height: 6.5rem; border: 1px solid var(--color-gold-400); border-radius: 50%; opacity: .45; pointer-events: none; }
        .wwa-rings::after { content: ""; position: absolute; inset: .75rem; border: 1px solid var(--color-gold-400); border-radius: 50%; }
        .wwa-photo { display: block; width: 100%; aspect-ratio: 4 / 5; object-fit: cover; object-position: center top; border-radius: 1.75rem; position: relative; }
        .wwa-quote { position: absolute; right: 0; bottom: 0; width: min(19rem, 85%); background: var(--color-brand-900); color: #fff; border-radius: 1.25rem; padding: 1.5rem 1.75rem; }
        .wwa-quote-author { color: var(--color-gold-400); }
        .wwa-stat { background: color-mix(in srgb, var(--color-brand-100) 70%, transparent); }
        @media (min-width: 768px) {
            .wwa-photo-wrap { padding: 0; margin-right: 3.5rem; }
            .wwa-quote { right: -3.5rem; bottom: -1.75rem; }
        }
    </style>
@endonce
<section id="who-we-are" class="mw-6xl mx-auto px-6 py-16">
    <div class="d-grid grid-cols-md-2 gap-12 align-items-center">
        <div class="reveal wwa-photo-wrap">
            <span class="wwa-rings" aria-hidden="true"></span>
            @if ($section->image_path)
                <img src="{{ asset('storage/'.$section->image_path) }}" alt="{{ $section->quote_author ?: $section->title }}"
                     class="wwa-photo premium-shadow" loading="lazy">
            @endif
            @if ($section->quote)
                <figure class="wwa-quote premium-shadow m-0">
                    <blockquote class="font-display fst-italic lh-base m-0">&ldquo;{{ $section->quote }}&rdquo;</blockquote>
                    @if ($section->quote_author)
                        <figcaption class="wwa-quote-author fs-sm fw-semibold mt-3">&mdash; {{ $section->quote_author }}</figcaption>
                    @endif
                </figure>
            @endif
        </div>

        <div class="reveal" style="transition-delay: 0.1s">
            @if ($section->subtitle)
                <p class="fs-xs fw-semibold text-uppercase tracking-widest text-gold mb-3">{{ $section->subtitle }}</p>
            @endif
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-5">{{ $section->title }}</h2>
            @if ($section->content)
                <div class="editor-content text-secondary">{!! $section->content !!}</div>
            @endif

            @if ($stats)
                <div class="d-grid grid-cols-{{ min(count($stats), 3) }} gap-3 mt-8">
                    @foreach ($stats as $stat)
                        <div class="wwa-stat rounded-4 px-3 py-4 text-center">
                            <p class="font-display fs-2xl fw-bold text-brand-emphasis lh-sm">{{ $stat['value'] }}</p>
                            <p class="fs-xs text-secondary mt-1">{{ $stat['label'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center gap-3 mt-8">
                @if ($section->cta_label && $section->cta_url)
                    <a href="{{ $section->cta_url }}"
                       class="d-inline-flex align-items-center gap-2 text-white px-7 py-3 rounded-pill fw-semibold premium-shadow"
                       style="background: var(--color-brand-900)">
                        {{ $section->cta_label }}
                    </a>
                @endif
                <a href="{{ $talkUrl }}" @if ($whatsapp) target="_blank" rel="noopener" @endif
                   class="d-inline-flex align-items-center gap-2 bg-body text-brand-emphasis border px-7 py-3 rounded-pill fw-semibold">
                    <x-icon name="chat-bubble" class="w-5 h-5 text-brand" />
                    Talk to Us
                </a>
            </div>
        </div>
    </div>
</section>
