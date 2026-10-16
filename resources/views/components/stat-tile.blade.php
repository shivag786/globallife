@props([
    'label',
    'value' => null,
    'icon' => null,
    'color' => 'brand',
    'href' => null,
    'hint' => null,
    'cta' => null,
    'money' => false,
    'suffix' => '',
])

@php
    // The accent is a single class; the colours themselves live in the
    // stylesheet (.stat-tile--{accent}) rather than as literal class strings
    // here. A linked tile lifts on hover and reveals its call to action, which
    // is also CSS — nothing to vary in the markup.
    $accents = ['brand', 'emerald', 'teal', 'sky', 'indigo', 'violet', 'amber', 'rose', 'cyan', 'slate'];
    $accent = in_array($color, $accents, true) ? $color : 'brand';
    $display = $money ? '₹'.number_format((float) $value) : null;
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(['stat-tile', 'stat-tile--'.$accent]) }}>
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div class="min-w-0">
            <p class="stat-tile__label">{{ $label }}</p>
            @if ($money)
                <p class="stat-tile__value text-truncate">{{ $display }}</p>
            @elseif (! is_null($value))
                <p class="stat-tile__value" data-countup="{{ $value }}{{ $suffix }}">0</p>
            @endif
            @isset($extra){{ $extra }}@endisset
            @if ($hint)
                <p class="small text-secondary mt-1 mb-0">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <span class="stat-tile__chip">
                <x-icon :name="$icon" class="w-6 h-6" />
            </span>
        @endif
    </div>
    @if ($href && $cta)
        <span class="stat-tile__cta">
            {{ $cta }} <x-icon name="arrow-right" class="w-3.5 h-3.5" />
        </span>
    @endif
</{{ $tag }}>
