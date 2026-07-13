@php
    $settings = app(\App\Services\SettingsService::class)->all();

    $siteName = $settings['site_title'] ?? config('app.name');
    $pageDescription = $metaDescription ?? $settings['meta_description'] ?? 'Global Life — wellness products, VIP memberships, and a city-managed distributor network across India.';
    $pageCanonical = $canonical ?? $settings['canonical_url'] ?? null;
    $ogTitleValue = $title ?? $settings['og_title'] ?? $siteName;
    $ogDescriptionValue = $metaDescription ?? $settings['og_description'] ?? $pageDescription;
    $ogImageValue = $ogImage ?? (! empty($settings['og_image']) ? asset('storage/'.$settings['og_image']) : null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $siteName }}{{ isset($title) ? ' · '.$siteName : '' }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if (! empty($settings['meta_keywords']))
        <meta name="keywords" content="{{ $settings['meta_keywords'] }}">
    @endif
    @if ($pageCanonical)
        <link rel="canonical" href="{{ $pageCanonical }}">
    @endif
    @if (! empty($settings['robots']))
        <meta name="robots" content="{{ $settings['robots'] }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitleValue }}">
    <meta property="og:description" content="{{ $ogDescriptionValue }}">
    @if ($ogImageValue)
        <meta property="og:image" content="{{ $ogImageValue }}">
    @endif

    <meta name="twitter:card" content="{{ $settings['twitter_card'] ?? 'summary_large_image' }}">
    @if (! empty($settings['twitter_site']))
        <meta name="twitter:site" content="{{ $settings['twitter_site'] }}">
    @endif
    <meta name="twitter:title" content="{{ $ogTitleValue }}">
    <meta name="twitter:description" content="{{ $ogDescriptionValue }}">
    @if ($ogImageValue)
        <meta name="twitter:image" content="{{ $ogImageValue }}">
    @endif

    @if (! empty($settings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    {{ $head ?? '' }}
</head>
<body class="bg-cream text-slate-800 antialiased">
    @if (! empty($settings['gtm_id']))
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ $settings['gtm_id'] }}"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    @endif
    <header class="sticky top-0 z-30 bg-cream/90 backdrop-blur border-b border-slate-200/60">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-display text-2xl font-bold text-brand-800">
                @if (! empty($settings['site_logo']))
                    <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}" class="h-10 w-auto">
                @else
                    {{ $siteName }}
                @endif
            </a>
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="{{ url('/') }}" class="hover:text-brand-600">Home</a>
                <a href="{{ route('products.index') }}" class="hover:text-brand-600">Products</a>
                <a href="{{ route('blog.index') }}" class="hover:text-brand-600">Blog</a>
                <a href="{{ route('events.index') }}" class="hover:text-brand-600">Events</a>
                <a href="{{ route('vip-plans.index') }}" class="hover:text-brand-600">VIP Plans</a>
                <a href="{{ route('contact') }}" class="hover:text-brand-600">Contact</a>
            </nav>
            <div class="flex items-center gap-3 sm:gap-4 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline font-medium text-brand-700 hover:text-brand-800">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline font-medium text-brand-700 hover:text-brand-800">Login</a>
                @endauth
                <a href="{{ route('vip-plans.index') }}" class="hidden sm:inline-block bg-brand-700 text-white px-4 py-2 rounded-full hover:bg-brand-800 transition">
                    Become a VIP
                </a>
                <button type="button" class="md:hidden text-slate-600" data-mobile-nav-open aria-label="Open menu">
                    <x-icon name="bars" class="w-6 h-6" />
                </button>
            </div>
        </div>

        <div id="mobile-nav" class="hidden md:hidden border-t border-slate-200/60 bg-cream">
            <nav class="px-6 py-4 flex flex-col gap-3 text-sm font-medium text-slate-600">
                <a href="{{ url('/') }}" class="hover:text-brand-600">Home</a>
                <a href="{{ route('products.index') }}" class="hover:text-brand-600">Products</a>
                <a href="{{ route('blog.index') }}" class="hover:text-brand-600">Blog</a>
                <a href="{{ route('events.index') }}" class="hover:text-brand-600">Events</a>
                <a href="{{ route('vip-plans.index') }}" class="hover:text-brand-600">VIP Plans</a>
                <a href="{{ route('contact') }}" class="hover:text-brand-600">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-600">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-brand-600">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="bg-brand-950 text-brand-100 mt-20">
        <div class="max-w-6xl mx-auto px-6 py-16 grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div>
                <p class="font-display text-xl font-bold text-white mb-3">{{ $siteName }}</p>
                <p class="text-sm text-brand-200">A homegrown brand built on transparency, quality, and people — wellness products and a nationwide distributor community.</p>
            </div>
            <div>
                <p class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Company</p>
                <ul class="space-y-2 text-sm text-brand-200">
                    <li><a href="{{ url('/') }}#about" class="hover:text-white">About Us</a></li>
                    <li><a href="{{ url('/') }}#opportunity" class="hover:text-white">Business Opportunity</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white">Events</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                    <li><a href="{{ url('/') }}#testimonials" class="hover:text-white">Testimonials</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Products</p>
                <ul class="space-y-2 text-sm text-brand-200">
                    <li><a href="{{ route('products.index') }}" class="hover:text-white">All Products</a></li>
                    <li><a href="{{ route('vip-plans.index') }}" class="hover:text-white">VIP Plans</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Contact</p>
                <ul class="space-y-2 text-sm text-brand-200">
                    @if ($settings['contact_address'] ?? null)
                        <li>{{ $settings['contact_address'] }}</li>
                    @endif
                    @if ($settings['contact_email'] ?? null)
                        <li><a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-white">{{ $settings['contact_email'] }}</a></li>
                    @endif
                    @if ($settings['contact_whatsapp'] ?? null)
                        <li><a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}" class="hover:text-white">WhatsApp: {{ $settings['contact_whatsapp'] }}</a></li>
                    @endif
                </ul>
                <div class="flex gap-3 mt-4">
                    @foreach (['social_facebook' => 'FB', 'social_instagram' => 'IG', 'social_youtube' => 'YT', 'social_linkedin' => 'IN'] as $key => $label)
                        @if ($settings[$key] ?? null)
                            <a href="{{ $settings[$key] }}" class="w-8 h-8 rounded-full bg-brand-800 flex items-center justify-center text-xs hover:bg-brand-700">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="border-t border-brand-800">
            <div class="max-w-6xl mx-auto px-6 py-6 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-brand-300">
                <p>&copy; {{ now()->year }} {{ $siteName }}. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-white">Privacy Policy</a>
                    <a href="#" class="hover:text-white">Terms of Service</a>
                    <a href="#" class="hover:text-white">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    @if ($settings['contact_whatsapp'] ?? null)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
           class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-green-500 text-white flex items-center justify-center shadow-lg hover:bg-green-600 hover:scale-110 transition animate-pulse-ring"
           title="Chat on WhatsApp">
            <x-icon name="chat-bubble" class="w-7 h-7" />
        </a>
    @endif

    <x-chatbot />
</body>
</html>
