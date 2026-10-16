@php
    $statIcons = ['star', 'users', 'map-pin', 'check-circle', 'sparkles', 'rupee'];
@endphp
<div class="bg-brand py-16">
    <div class="mw-6xl mx-auto px-6">
        @if ($section->title)
            <h2 class="font-display fs-2xl fw-bold text-center text-white mb-10 reveal">{{ $section->title }}</h2>
        @endif
        <div class="d-grid grid-cols-2 grid-cols-md-3 gap-8 text-center">
            @foreach ($section->items ?? [] as $index => $item)
                <div class="reveal" style="transition-delay: {{ $index * 0.1 }}s">
                    <x-icon name="{{ $statIcons[$index % count($statIcons)] }}" class="w-6 h-6 mx-auto mb-2 text-gold" />
                    <p class="fs-4xl fw-bolder text-gold" data-countup="{{ $item['value'] ?? '' }}">0</p>
                    <p class="fs-sm text-brand mt-2">{{ $item['label'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
