@php
    $certIcons = ['shield-check', 'beaker', 'leaf', 'truck', 'check-circle', 'star'];
@endphp
<section class="bg-white border-y border-slate-100">
    <div class="max-w-6xl mx-auto px-6 py-12">
        @if ($section->title)
            <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 mb-9 reveal">{{ $section->title }}</p>
        @endif
        <div class="flex flex-wrap justify-center gap-x-10 gap-y-8">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal flex flex-col items-center text-center gap-2 w-32" style="transition-delay: {{ ($index % 6) * 0.08 }}s">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-50 to-gold-400/10 border border-brand-100 flex items-center justify-center">
                        <x-icon name="{{ $certIcons[$index % count($certIcons)] }}" class="w-7 h-7 text-brand-600" />
                    </div>
                    <p class="text-sm font-semibold text-brand-900 leading-tight">{{ $item['title'] ?? '' }}</p>
                    @if ($item['description'] ?? null)
                        <p class="text-xs text-slate-400 leading-tight">{{ $item['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
