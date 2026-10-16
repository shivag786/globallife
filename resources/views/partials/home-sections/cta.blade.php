<div class="mw-5xl mx-auto px-6 my-16">
    <div class="reveal py-16 px-8 text-center rounded-4 text-white premium-shadow position-relative overflow-hidden" style="background: linear-gradient(to bottom right, var(--color-brand-700), var(--color-brand-900))">
        <div class="position-absolute top-n10 end-n10 w-48 h-48 rounded-pill animate-float" style="background-color: color-mix(in srgb, var(--color-gold-400) 10%, transparent)" aria-hidden="true"></div>
        <h2 class="font-display fs-3xl fw-bold mb-3">{{ $section->title }}</h2>
        @if ($section->subtitle)
            <p class="text-brand- mb-8">{{ $section->subtitle }}</p>
        @endif
        @if ($section->cta_label && $section->cta_url)
            <a href="{{ $section->cta_url }}" class="btn btn-gold rounded-pill d-inline-flex align-items-center gap-2 text-brand-emphasis">
                {{ $section->cta_label }} <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        @endif
    </div>
</div>
