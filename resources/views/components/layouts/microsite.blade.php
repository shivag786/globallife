@props(['microsite', 'navSections' => [], 'title' => null, 'metaDescription' => null])

@php
    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? config('app.name');
    $pageDescription = $metaDescription ?? $microsite->short_description ?? $microsite->description;
    $ogImageValue = $microsite->cover_banner_path ? asset('storage/'.$microsite->cover_banner_path) : (! empty($settings['og_image']) ? asset('storage/'.$settings['og_image']) : null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ $siteName }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    <meta property="og:type" content="business.business">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    @if ($ogImageValue)
        <meta property="og:image" content="{{ $ogImageValue }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    @if (! empty($settings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    @if (! empty($settings['gtm_id']))
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0], j=d.createElement(s), dl=l!='dataLayer'?'&l='+l:'';
            j.async=true; j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl; f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $settings['gtm_id'] }}');
        </script>
    @endif
    @if (! empty($settings['ga_measurement_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings['ga_measurement_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '{{ $settings['ga_measurement_id'] }}');
        </script>
    @endif
</head>
<body class="bg-cream" id="msite-body">
    @if (! empty($settings['gtm_id']))
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ $settings['gtm_id'] }}"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    @endif

    <header id="msite-nav" class="sticky-top msite-glass border-bottom">
        <div class="msite-container d-flex align-items-center justify-content-between" style="height: 4rem">
            <a href="#top" class="d-flex align-items-center gap-2 min-w-0 text-decoration-none">
                @if ($microsite->logo_path)
                    <img src="{{ asset('storage/'.$microsite->logo_path) }}" alt="{{ $microsite->business_name }}"
                         class="rounded-circle object-fit-cover flex-shrink-0" width="36" height="36">
                @else
                    <span class="font-heading fw-bold text-truncate" style="color: var(--color-brand-950)">
                        {{ $microsite->business_name }}
                    </span>
                @endif
            </a>

            <nav class="d-none d-lg-flex align-items-center gap-4 small fw-medium text-secondary">
                @foreach ($navSections as $anchor => $label)
                    <a href="#{{ $anchor }}" data-msite-nav-link="{{ $anchor }}"
                       class="msite-nav-link text-reset">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="d-flex align-items-center gap-2">
                @include('partials.cart-icon')
                @if ($microsite->phone_number)
                    <a href="{{ route('microsite.click', [$microsite, 'call']) }}"
                       class="d-none d-sm-inline-flex msite-btn msite-btn-primary msite-btn-sm">
                        <x-icon name="phone" class="w-4 h-4" /> Call Now
                    </a>
                @endif
                <button type="button" class="btn btn-link text-secondary p-0 d-lg-none"
                        data-msite-menu-open aria-label="Open menu">
                    <x-icon name="bars" class="w-6 h-6" />
                </button>
            </div>
        </div>

        <div id="msite-mobile-nav" class="d-none border-top bg-body">
            <nav class="msite-container py-3 d-flex flex-column gap-1 small fw-medium">
                @foreach ($navSections as $anchor => $label)
                    <a href="#{{ $anchor }}" data-msite-nav-link="{{ $anchor }}"
                       class="px-2 py-2 rounded text-secondary text-decoration-none">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <x-flash />

    <main id="top">
        {{ $slot }}
    </main>

    <footer class="py-4 text-center small" style="background: var(--color-brand-950); color: var(--color-brand-200)">
        <div class="msite-container">
            <p class="mb-1">{{ $microsite->business_name }} &middot; {{ $microsite->city->name }}</p>
            <p class="mb-0" style="font-size: 0.8125rem; color: var(--color-brand-400)">
                Powered by <a href="{{ url('/') }}" class="text-reset">{{ $siteName }}</a>
            </p>
        </div>
    </footer>

    <button type="button" id="msite-back-to-top" data-msite-back-to-top
            class="btn btn-link p-0 text-decoration-none msite-float-btn is-hidden position-fixed rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm border-0"
            style="bottom: 6rem; right: 1.5rem; z-index: 1030; width: 3rem; height: 3rem; background: var(--color-brand-900)"
            aria-label="Back to top">
        <x-icon name="arrow-up" class="w-5 h-5" />
    </button>

    @include('partials.microsite.floating-buttons')
</body>
</html>
