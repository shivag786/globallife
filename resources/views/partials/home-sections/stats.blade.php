@php
    $statIcons = ['star', 'users', 'map-pin', 'check-circle', 'sparkles', 'rupee'];
@endphp
<div class="bg-brand-900 py-16">
    <div class="max-w-6xl mx-auto px-6">
        @if ($section->title)
            <h2 class="font-display text-2xl font-bold text-center text-white mb-10 reveal">{{ $section->title }}</h2>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-3 gap-8 text-center">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal" style="transition-delay: {{ $index * 0.1 }}s">
                    <x-icon name="{{ $statIcons[$index % count($statIcons)] }}" class="w-6 h-6 mx-auto mb-2 text-gold-400" />
                    <p class="text-4xl font-extrabold text-gold-400" data-countup="{{ $item['value'] ?? '' }}">0</p>
                    <p class="text-sm text-brand-200 mt-2">{{ $item['label'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
