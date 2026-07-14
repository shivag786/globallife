@props([
    'id',
    'type' => 'area',
    'height' => 300,
    'series' => [],
    'categories' => [],
    'labels' => [],
    'colors' => [],
    'currency' => false,
])

@php
    $apexConfig = [
        'type' => $type,
        'height' => (int) $height,
        'series' => $series,
        'categories' => $categories,
        'labels' => $labels,
        'colors' => $colors,
        'currency' => (bool) $currency,
    ];
@endphp

{{-- Container the JS renderer targets, plus its config as inert JSON picked up by
     resources/js/charts/init.js (data generated server-side, rendered client-side).
     @json() escapes HTML/quotes, so partner/city names in labels can't break out. --}}
<div id="{{ $id }}" class="w-full"></div>
<script type="application/json" data-apex-for="{{ $id }}">@json($apexConfig)</script>
