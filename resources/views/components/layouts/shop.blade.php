@props(['title' => null, 'storefront' => null])

@php
    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? config('app.name');
    // An explicit storefront (e.g. the order's store on the confirmation page) wins;
    // otherwise derive it from the current cart.
    $storefront = $storefront ?? app(\App\Services\StorefrontContext::class)->current();
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ $storefront->business_name ?? $siteName }}</title>
    <meta name="robots" content="noindex">

    @if (! empty($settings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-cream">
    <x-flash />

    <header class="sticky-top border-bottom" style="background: rgb(248 245 239 / 0.9); backdrop-filter: blur(8px)">
        <div class="container-lg d-flex align-items-center justify-content-between gap-3" style="height: 4rem">
            @if ($storefront)
                {{-- Shopping a VIP storefront → keep the customer in that store's context. --}}
                <a href="{{ url($storefront->publicPath()) }}"
                   class="d-flex align-items-center gap-2 min-w-0 text-decoration-none">
                    @if ($storefront->logo_path)
                        <img src="{{ asset('storage/'.$storefront->logo_path) }}" alt="{{ $storefront->business_name }}"
                             class="rounded-circle object-fit-cover flex-shrink-0" width="36" height="36">
                    @else
                        <span class="rounded-circle bg-brand text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                              style="width: 2.25rem; height: 2.25rem">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($storefront->business_name, 0, 1)) }}
                        </span>
                    @endif
                    <span class="font-heading fw-bold text-truncate" style="color: var(--color-brand-900)">
                        {{ $storefront->business_name }}
                    </span>
                </a>
                <div class="d-flex align-items-center gap-3 gap-sm-4 flex-shrink-0">
                    <a href="{{ url($storefront->publicPath()) }}#shop"
                       class="d-none d-sm-inline small fw-medium text-secondary text-decoration-none">← Continue Shopping</a>
                    @include('partials.cart-icon')
                </div>
            @else
                <a href="{{ url('/') }}"
                   class="d-flex align-items-center gap-2 font-display fs-3 fw-bold text-decoration-none"
                   style="color: var(--color-brand-800)">
                    @if (! empty($settings['site_logo']))
                        <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}"
                             style="height: 2.5rem; width: auto">
                    @else
                        {{ $siteName }}
                    @endif
                </a>
                <div class="d-flex align-items-center gap-3 gap-sm-4">
                    <a href="{{ route('products.index') }}"
                       class="d-none d-sm-inline small fw-medium text-secondary text-decoration-none">← Continue Shopping</a>
                    @include('partials.cart-icon')
                </div>
            @endif
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-top mt-5">
        <div class="container-lg py-4 text-center small text-secondary">
            @if ($storefront)
                <p class="mb-1">{{ $storefront->business_name }}{{ $storefront->city ? ' · '.$storefront->city->name : '' }}</p>
                <p class="mb-0" style="font-size: 0.8125rem">
                    Powered by <a href="{{ url('/') }}" class="text-decoration-none">{{ $siteName }}</a>
                </p>
            @else
                <p class="mb-0">© {{ date('Y') }} {{ $siteName }}</p>
            @endif
        </div>
    </footer>
</body>
</html>
