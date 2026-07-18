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

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-slate-800 antialiased">
    <x-flash />

    <header class="sticky top-0 z-30 bg-cream/90 backdrop-blur border-b border-slate-200/60">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between gap-4">
            @if ($storefront)
                {{-- Shopping a VIP storefront → keep the customer in that store's context. --}}
                <a href="{{ url($storefront->publicPath()) }}" class="flex items-center gap-2.5 min-w-0">
                    @if ($storefront->logo_path)
                        <img src="{{ asset('storage/'.$storefront->logo_path) }}" alt="{{ $storefront->business_name }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                    @else
                        <span class="w-9 h-9 rounded-full bg-brand-700 text-white flex items-center justify-center font-bold flex-shrink-0">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($storefront->business_name, 0, 1)) }}</span>
                    @endif
                    <span class="font-heading font-bold text-brand-900 truncate">{{ $storefront->business_name }}</span>
                </a>
                <div class="flex items-center gap-4 sm:gap-6 flex-shrink-0">
                    <a href="{{ url($storefront->publicPath()) }}#shop" class="hidden sm:inline text-sm font-medium text-slate-600 hover:text-brand-700">← Continue Shopping</a>
                    @include('partials.cart-icon')
                </div>
            @else
                <a href="{{ url('/') }}" class="flex items-center gap-2 font-display text-2xl font-bold text-brand-800">
                    @if (! empty($settings['site_logo']))
                        <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}" class="h-10 w-auto">
                    @else
                        {{ $siteName }}
                    @endif
                </a>
                <div class="flex items-center gap-4 sm:gap-6">
                    <a href="{{ route('products.index') }}" class="hidden sm:inline text-sm font-medium text-slate-600 hover:text-brand-700">← Continue Shopping</a>
                    @include('partials.cart-icon')
                </div>
            @endif
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200/60 mt-16">
        <div class="max-w-6xl mx-auto px-6 py-8 text-center text-sm text-slate-400">
            @if ($storefront)
                <p>{{ $storefront->business_name }}{{ $storefront->city ? ' · '.$storefront->city->name : '' }}</p>
                <p class="text-xs mt-1">Powered by <a href="{{ url('/') }}" class="hover:text-brand-700">{{ $siteName }}</a></p>
            @else
                <p>© {{ date('Y') }} {{ $siteName }}</p>
            @endif
        </div>
    </footer>
</body>
</html>
