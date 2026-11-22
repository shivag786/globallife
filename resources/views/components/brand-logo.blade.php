@props([
    // light = dark-green text for cream headers; dark = white text for the dashboard sidebar.
    'variant' => 'light',
])

@php
    $brandSettings = app(\App\Services\SettingsService::class)->all();
    $brandName = $brandSettings['site_title'] ?? config('app.name');

    // The emblem shown large beside the name: the uploaded "Header logo icon",
    // else the emblem shipped with the site, else the full logo on its own.
    $brandIcon = match (true) {
        ! empty($brandSettings['site_logo_icon']) => asset('storage/'.$brandSettings['site_logo_icon']),
        is_file(public_path('images/logo-emblem.png')) => asset('images/logo-emblem.png'),
        default => null,
    };
    $fullLogo = ! empty($brandSettings['site_logo']) ? asset('storage/'.$brandSettings['site_logo']) : null;
@endphp

<span {{ $attributes->class(['brand-logo', 'brand-logo--'.$variant]) }}>
    @if ($brandIcon)
        <img src="{{ $brandIcon }}" alt="" class="brand-logo__icon">
        <span class="brand-logo__name">{{ $brandName }}</span>
    @elseif ($fullLogo)
        <img src="{{ $fullLogo }}" alt="{{ $brandName }}" class="site-logo">
    @else
        <span class="brand-logo__name">{{ $brandName }}</span>
    @endif
</span>
