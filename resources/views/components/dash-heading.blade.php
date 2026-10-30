@props([
    'title',
    'icon' => 'sparkles',
    'subtitle' => null,
    'tone' => 'brand',
])

{{-- Section heading on the panel dashboards. Tones: brand, gold, emerald, slate. --}}
<div {{ $attributes->class(['dash-heading', 'dash-heading--'.$tone]) }}>
    <span class="dash-heading__icon">
        <x-icon :name="$icon" class="w-4 h-4" />
    </span>
    <div class="min-w-0">
        <h2 class="dash-heading__title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="dash-heading__sub">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
