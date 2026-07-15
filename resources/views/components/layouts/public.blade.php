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

    @if (! empty($settings['announcement_text']))
        <div class="bg-gradient-to-r from-brand-800 via-brand-900 to-brand-950 text-cream text-center text-sm py-2.5 px-4">
            @if (! empty($settings['announcement_link']))
                <a href="{{ $settings['announcement_link'] }}" class="inline-flex items-center gap-2 hover:text-gold-400 transition">
                    <x-icon name="sparkles" class="w-4 h-4 text-gold-400" />
                    {{ $settings['announcement_text'] }}
                    <span class="underline underline-offset-2 font-medium">Learn more</span>
                </a>
            @else
                <span class="inline-flex items-center gap-2">
                    <x-icon name="sparkles" class="w-4 h-4 text-gold-400" />
                    {{ $settings['announcement_text'] }}
                </span>
            @endif
        </div>
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
                @php $cartCount = app(\App\Services\CartService::class)->count(); @endphp
                <a href="{{ route('cart.index') }}" class="relative text-slate-600 hover:text-brand-700" aria-label="Cart">
                    <x-icon name="shopping-bag" class="w-6 h-6" />
                    @if ($cartCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-brand-700 text-white text-[0.6rem] font-bold rounded-full min-w-4 h-4 px-1 flex items-center justify-center">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    @endif
                </a>
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

    <x-flash />

    <main>
        {{ $slot }}
    </main>

    <footer class="bg-brand-950 text-brand-100 mt-20">
        {{-- Newsletter band --}}
        <div class="max-w-6xl mx-auto px-6">
            <div class="relative -translate-y-8 bg-gradient-to-br from-brand-700 to-brand-900 rounded-3xl px-8 py-10 md:px-12 md:py-12 premium-shadow border border-brand-700/40">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="font-display text-2xl md:text-3xl font-bold text-white mb-2">Join the {{ $siteName }} family</h3>
                        <p class="text-sm text-brand-100/80">Wellness tips, new launches, and member-only offers — straight to your inbox. No spam, ever.</p>
                    </div>
                    <div>
                        @if (session('status'))
                            <div class="mb-3 text-sm text-white bg-white/10 border border-white/20 rounded-lg px-4 py-2.5">{{ session('status') }}</div>
                        @endif
                        <form method="POST" action="{{ route('enquiry.store') }}" class="flex flex-col sm:flex-row gap-3">
                            @csrf
                            <input type="hidden" name="source" value="homepage">
                            <input type="hidden" name="name" value="Newsletter Subscriber">
                            <input type="hidden" name="message" value="Newsletter subscription from footer">
                            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <input type="email" name="email" required placeholder="Enter your email"
                                   class="flex-1 rounded-full border-0 px-5 py-3 text-sm text-slate-800 focus:ring-2 focus:ring-gold-400 focus:outline-none">
                            <button type="submit" class="bg-gold-500 text-brand-950 font-semibold px-6 py-3 rounded-full hover:bg-gold-400 transition whitespace-nowrap">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-6 pb-12 grid sm:grid-cols-2 lg:grid-cols-5 gap-10">
            <div class="lg:col-span-2">
                <p class="font-display text-xl font-bold text-white mb-3">{{ $siteName }}</p>
                <p class="text-sm text-brand-200 max-w-xs">A homegrown brand built on transparency, quality, and people — wellness products and a nationwide distributor community.</p>
                <div class="flex gap-3 mt-5">
                    @foreach (['social_facebook' => 'FB', 'social_instagram' => 'IG', 'social_youtube' => 'YT', 'social_linkedin' => 'IN'] as $key => $label)
                        @if ($settings[$key] ?? null)
                            <a href="{{ $settings[$key] }}" class="w-9 h-9 rounded-full bg-brand-800 flex items-center justify-center text-xs hover:bg-gold-500 hover:text-brand-950 transition">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Company</p>
                <ul class="space-y-2 text-sm text-brand-200">
                    <li><a href="{{ url('/') }}#about" class="hover:text-white">About Us</a></li>
                    <li><a href="{{ url('/') }}#opportunity" class="hover:text-white">Business Opportunity</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white">Events</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold text-white mb-3 uppercase tracking-wide">Explore</p>
                <ul class="space-y-2 text-sm text-brand-200">
                    <li><a href="{{ route('products.index') }}" class="hover:text-white">All Products</a></li>
                    <li><a href="{{ route('vip-plans.index') }}" class="hover:text-white">VIP Plans</a></li>
                    <li><a href="{{ url('/') }}#testimonials" class="hover:text-white">Testimonials</a></li>
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
            </div>
        </div>

        {{-- Trust + payment badges --}}
        <div class="border-t border-brand-800/70">
            <div class="max-w-6xl mx-auto px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['SSL Secured', 'FSSAI Certified', 'Lab Tested', '100% Genuine'] as $trust)
                        <span class="inline-flex items-center gap-1.5 text-xs text-brand-200 bg-brand-900 border border-brand-800 px-3 py-1.5 rounded-full">
                            <x-icon name="shield-check" class="w-3.5 h-3.5 text-gold-400" /> {{ $trust }}
                        </span>
                    @endforeach
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['UPI', 'Visa', 'Mastercard', 'RuPay', 'NetBanking'] as $pay)
                        <span class="text-[11px] font-semibold text-brand-300 bg-brand-900 border border-brand-800 px-2.5 py-1.5 rounded">{{ $pay }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-brand-800">
            <div class="max-w-6xl mx-auto px-6 py-6 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-brand-300">
                <p>&copy; {{ now()->year }} {{ $siteName }}. All rights reserved. &middot; Made in India 🇮🇳</p>
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
