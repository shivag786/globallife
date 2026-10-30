@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

{{-- A titled panel on the dashboards (charts, lists, tables). --}}
<div {{ $attributes->class('dash-card') }}>
    <div class="dash-card__head">
        @if ($icon)
            <span class="dash-card__icon"><x-icon :name="$icon" class="w-5 h-5" /></span>
        @endif
        <div class="min-w-0">
            <h2 class="dash-card__title">{{ $title }}</h2>
            @if ($subtitle)
                <p class="dash-card__sub">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    {{ $slot }}
</div>
