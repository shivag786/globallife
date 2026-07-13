<div class="max-w-5xl mx-auto px-6 my-16">
    <div class="reveal py-16 px-8 text-center bg-gradient-to-br from-brand-700 to-brand-900 rounded-3xl text-white premium-shadow relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-gold-400/10 rounded-full blur-2xl animate-float" aria-hidden="true"></div>
        <h2 class="font-display text-3xl font-bold mb-3">{{ $section->title }}</h2>
        @if ($section->subtitle)
            <p class="text-brand-100 mb-8">{{ $section->subtitle }}</p>
        @endif
        @if ($section->cta_label && $section->cta_url)
            <a href="{{ $section->cta_url }}" class="inline-flex items-center gap-2 bg-gold-500 text-brand-950 px-8 py-3.5 rounded-full font-semibold hover:bg-gold-400 hover:-translate-y-0.5 transition">
                {{ $section->cta_label }} <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        @endif
    </div>
</div>
