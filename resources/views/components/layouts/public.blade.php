@php
    $settings = app(\App\Services\SettingsService::class)->all();

    $siteName = $settings['site_title'] ?? config('app.name');
    $pageDescription = $metaDescription ?? $settings['meta_description'] ?? 'Global Life — wellness products, VIP memberships, and a city-managed distributor network across India.';
    $pageCanonical = $canonical ?? $settings['canonical_url'] ?? null;
    $ogTitleValue = $title ?? $settings['og_title'] ?? $siteName;
    $ogDescriptionValue = $metaDescription ?? $settings['og_description'] ?? $pageDescription;
    $ogImageValue = $ogImage ?? (! empty($settings['og_image']) ? asset('storage/'.$settings['og_image']) : null);

    // Logged-in user info for header avatar menu
    $authUser = auth()->user();
    $userInitials = 'U';
    if ($authUser) {
        $userInitials = \Illuminate\Support\Str::of($authUser->name ?? '')
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w, 0, 1)))
            ->implode('') ?: 'U';
    }

    // Main menu. `active` decides which tab is highlighted for the current page.
    $navItems = [
        ['label' => 'Home', 'url' => url('/'), 'active' => request()->is('/')],
        ['label' => 'Products', 'url' => route('products.index'), 'active' => request()->routeIs('products.*')],
        ['label' => 'Blog', 'url' => route('blog.index'), 'active' => request()->routeIs('blog.*')],
        ['label' => 'Events', 'url' => route('events.index'), 'active' => request()->routeIs('events.*')],
        ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact*')],
    ];
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


    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @include('partials.typography')

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
<body class="bg-cream">
    @if (! empty($settings['gtm_id']))
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ $settings['gtm_id'] }}"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    @endif

    @if (! empty($settings['announcement_text']))
        <div class="text-center small py-2 px-3"
             style="background: linear-gradient(90deg, var(--color-brand-800), var(--color-brand-900), var(--color-brand-950)); color: var(--color-cream)">
            @if (! empty($settings['announcement_link']))
                <a href="{{ $settings['announcement_link'] }}"
                   class="d-inline-flex align-items-center gap-2 text-reset text-decoration-none">
                    <x-icon name="sparkles" class="w-4 h-4" style="color: var(--color-gold-400)" />
                    {{ $settings['announcement_text'] }}
                    <span class="text-decoration-underline fw-medium">Learn more</span>
                </a>
            @else
                <span class="d-inline-flex align-items-center gap-2">
                    <x-icon name="sparkles" class="w-4 h-4" style="color: var(--color-gold-400)" />
                    {{ $settings['announcement_text'] }}
                </span>
            @endif
        </div>
    @endif

    <header class="sticky-top border-bottom" style="background: rgb(248 245 239 / 0.9); backdrop-filter: blur(8px)">
        <div class="container-lg py-2 d-flex align-items-center justify-content-between gap-3">
            <a href="{{ url('/') }}" class="text-decoration-none min-w-0" aria-label="{{ $siteName }} home">
                <x-brand-logo />
            </a>
            <nav class="d-none d-md-flex align-items-center gap-4 fw-medium">
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" @class(['site-nav-link', 'active' => $item['active']])
                       @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="d-flex align-items-center gap-3 small">
                @include('partials.wishlist-icon')
                @include('partials.cart-icon')

                @auth
                    {{-- Logged-in user menu: avatar + name + dashboard + logout --}}
                    {{-- Bootstrap's own dropdown: its JS is already loaded globally,
                         so the hand-rolled open/close script this used to need is gone. --}}
                    <div class="dropdown" data-user-menu>
                        <button type="button" class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-1 rounded-pill"
                                data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <span class="rounded-circle bg-brand text-white d-flex align-items-center justify-content-center fw-semibold user-select-none"
                                  style="width: 2.25rem; height: 2.25rem; font-size: 0.75rem">
                                {{ $userInitials }}
                            </span>
                            <span class="d-none d-sm-block fw-medium text-body text-truncate" style="max-width: 120px">
                                {{ $authUser->name }}
                            </span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow-sm">
                            <div class="px-3 py-2">
                                <p class="fw-semibold mb-0 text-truncate">{{ $authUser->name }}</p>
                                @if (! empty($authUser->email))
                                    <p class="small text-secondary mb-0 text-truncate">{{ $authUser->email }}</p>
                                @endif
                            </div>
                            <hr class="dropdown-divider">
                            <a href="{{ route('dashboard') }}" class="dropdown-item">Dashboard</a>
                            <a href="{{ route('account.password.edit') }}" class="dropdown-item">Change Password</a>
                            <hr class="dropdown-divider">
                            <form method="POST" action="{{ route('logout', [], false) }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="d-none d-sm-inline fw-medium text-decoration-none"
                       style="color: var(--color-brand-700)">Login</a>
                @endauth

                <button type="button" class="btn btn-link text-secondary p-0 d-md-none"
                        data-mobile-nav-open aria-label="Open menu">
                    <x-icon name="bars" class="w-6 h-6" />
                </button>
            </div>
        </div>

        <div id="mobile-nav" class="d-none border-top bg-cream">
            <nav class="container-lg py-3 d-flex flex-column gap-1 small fw-medium">
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" @class(['site-nav-mobile-link', 'active' => $item['active']])
                       @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('wishlist.index') }}" @class(['site-nav-mobile-link', 'active' => request()->routeIs('wishlist.*')])>Wishlist</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="site-nav-mobile-link">Dashboard</a>
                    <a href="{{ route('account.password.edit') }}" class="site-nav-mobile-link">Change Password</a>
                    <form method="POST" action="{{ route('logout', [], false) }}">
                        @csrf
                        <button type="submit" class="site-nav-mobile-link text-danger border-0 bg-transparent w-100 text-start">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="site-nav-mobile-link">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <x-flash />

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-5" style="background: var(--color-brand-950); color: var(--color-brand-100)">
        {{-- Newsletter band --}}
        <div class="container-lg">
            <div class="rounded-4 p-4 p-md-5 premium-shadow position-relative"
                 style="transform: translateY(-2rem); background: linear-gradient(135deg, var(--color-brand-700), var(--color-brand-900)); border: 1px solid rgb(36 90 63 / 0.4)">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        <h3 class="font-display fs-2 fw-bold text-white mb-2">Join the {{ $siteName }} family</h3>
                        <p class="small mb-0" style="color: rgb(227 241 233 / 0.8)">
                            Wellness tips, new launches, and member-only offers — straight to your inbox. No spam, ever.
                        </p>
                    </div>
                    <div class="col-md-6">
                        @if (session('status'))
                            <div class="alert alert-light bg-body bg-opacity-10 border-white border-opacity-25 text-white small py-2">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('enquiry.store') }}"
                              class="d-flex flex-column flex-sm-row gap-2">
                            @csrf
                            <input type="hidden" name="source" value="homepage">
                            <input type="hidden" name="name" value="Newsletter Subscriber">
                            <input type="hidden" name="message" value="Newsletter subscription from footer">
                            <div class="d-none" aria-hidden="true">
                                <input type="text" name="website" tabindex="-1" autocomplete="off">
                            </div>
                            <input type="email" name="email" required placeholder="Enter your email"
                                   class="form-control rounded-pill border-0 px-4">
                            <button type="submit" class="btn btn-gold fw-semibold rounded-pill px-4 text-nowrap"
                                    style="color: var(--color-brand-950)">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-lg pb-5">
            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <p class="font-display fs-5 fw-bold text-white mb-2">{{ $siteName }}</p>
                    <p class="small mb-0" style="max-width: 20rem; color: var(--color-brand-200)">
                        A homegrown brand built on transparency, quality, and people — wellness products and a
                        nationwide distributor community.
                    </p>
                    <div class="d-flex gap-2 mt-4">
                        @foreach (['social_facebook' => 'FB', 'social_instagram' => 'IG', 'social_youtube' => 'YT', 'social_linkedin' => 'IN'] as $key => $label)
                            @if ($settings[$key] ?? null)
                                <a href="{{ $settings[$key] }}"
                                   class="rounded-circle d-flex align-items-center justify-content-center text-white text-decoration-none"
                                   style="width: 2.25rem; height: 2.25rem; font-size: 0.75rem; background: var(--color-brand-800)">{{ $label }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <p class="small fw-semibold text-white mb-2 text-uppercase">Company</p>
                    <ul class="list-unstyled small mb-0" style="color: var(--color-brand-200)">
                        <li class="mb-2"><a href="{{ url('/') }}#about" class="text-reset text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="{{ url('/') }}#opportunity" class="text-reset text-decoration-none">Business Opportunity</a></li>
                        <li class="mb-2"><a href="{{ route('blog.index') }}" class="text-reset text-decoration-none">Blog</a></li>
                        <li class="mb-2"><a href="{{ route('events.index') }}" class="text-reset text-decoration-none">Events</a></li>
                        <li><a href="{{ route('contact') }}" class="text-reset text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <p class="small fw-semibold text-white mb-2 text-uppercase">Explore</p>
                    <ul class="list-unstyled small mb-0" style="color: var(--color-brand-200)">
                        <li class="mb-2"><a href="{{ route('products.index') }}" class="text-reset text-decoration-none">All Products</a></li>
                        <li><a href="{{ url('/') }}#testimonials" class="text-reset text-decoration-none">Testimonials</a></li>
                    </ul>
                </div>
                <div class="col-12 col-lg-3">
                    <p class="small fw-semibold text-white mb-2 text-uppercase">Contact</p>
                    <ul class="list-unstyled small mb-0" style="color: var(--color-brand-200)">
                        @if ($settings['contact_address'] ?? null)
                            <li class="mb-2">{{ $settings['contact_address'] }}</li>
                        @endif
                        @if ($settings['contact_email'] ?? null)
                            <li class="mb-2">
                                <a href="mailto:{{ $settings['contact_email'] }}" class="text-reset text-decoration-none">{{ $settings['contact_email'] }}</a>
                            </li>
                        @endif
                        @if ($settings['contact_email_hr'] ?? null)
                            <li class="mb-2">
                                <a href="mailto:{{ $settings['contact_email_hr'] }}" class="text-reset text-decoration-none">HR: {{ $settings['contact_email_hr'] }}</a>
                            </li>
                        @endif
                        @if ($settings['contact_whatsapp'] ?? null)
                            <li>
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
                                   class="text-reset text-decoration-none">WhatsApp: {{ $settings['contact_whatsapp'] }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Trust + payment badges --}}
        <div style="border-top: 1px solid rgb(31 72 52 / 0.7)">
            <div class="container-lg py-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @php
                        $fssaiBadge = filled($settings['fssai_license'] ?? null) ? 'FSSAI Lic. No. '.$settings['fssai_license'] : 'FSSAI Certified';
                    @endphp
                    @foreach (['SSL Secured', $fssaiBadge, 'Lab Tested', '100% Genuine'] as $trust)
                        <span class="d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1"
                              style="font-size: 0.75rem; color: var(--color-brand-200); background: var(--color-brand-900); border: 1px solid var(--color-brand-800)">
                            <x-icon name="shield-check" class="w-3.5 h-3.5" style="color: var(--color-gold-400)" /> {{ $trust }}
                        </span>
                    @endforeach
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @foreach (['UPI', 'Visa', 'Mastercard', 'RuPay', 'NetBanking'] as $pay)
                        <span class="fw-semibold rounded px-2 py-1"
                              style="font-size: 0.6875rem; color: var(--color-brand-300); background: var(--color-brand-900); border: 1px solid var(--color-brand-800)">{{ $pay }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--color-brand-800)">
            <div class="container-lg py-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2"
                 style="font-size: 0.75rem; color: var(--color-brand-300)">
                <p class="mb-0">&copy; {{ now()->year }} {{ $siteName }}. All rights reserved. &middot; Made in India 🇮🇳</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-reset text-decoration-none">Privacy Policy</a>
                    <a href="#" class="text-reset text-decoration-none">Terms of Service</a>
                    <a href="#" class="text-reset text-decoration-none">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Floating buttons, bottom-right: WhatsApp on top, the chat assistant
         (x-chatbot, fixed at the corner) right below it. --}}
    @if ($settings['contact_whatsapp'] ?? null)
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}" target="_blank" rel="noopener"
           class="position-fixed rounded-circle text-white d-flex align-items-center justify-content-center shadow animate-pulse-ring text-decoration-none"
           style="bottom: 5.75rem; right: 1.5rem; z-index: 1030; width: 3.5rem; height: 3.5rem; background: #25d366"
           title="Chat on WhatsApp" aria-label="Chat on WhatsApp">
            <x-whatsapp-icon class="w-8 h-8" />
        </a>
    @endif

    <x-chatbot />

</body>
</html>