@php
    use Illuminate\Support\Str;

    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? 'Global Life';
    $whatsapp = ! empty($settings['contact_whatsapp']) ? preg_replace('/\D/', '', $settings['contact_whatsapp']) : null;

    $money = fn ($n) => '₹' . number_format((float) $n, fmod((float) $n, 1) == 0.0 ? 0 : 2);

    // SEO / social meta, settings-driven with sensible fallbacks.
    $pageTitle = $siteName . ' — Your entire business, in one link';
    $pageDescription = $settings['meta_description']
        ?? "India's technology-first direct-selling ecosystem. Every partner gets a premium digital business microsite — real products, transparent revenue, one shareable link.";
    $pageCanonical = url('/experience');
    $ogTitle = $settings['og_title'] ?? $pageTitle;
    $ogDescription = $settings['og_description'] ?? $pageDescription;
    $ogImage = ! empty($settings['og_image']) ? asset('storage/'.$settings['og_image']) : null;

    // Hero "your link" showcase — real, live microsite.
    $heroCity = $heroMicrosite?->city?->slug ?? 'jhansi';
    $heroBiz = $heroMicrosite?->business_slug ?? 'your-business';
    $heroPath = $heroMicrosite?->publicPath();

    $tierBlurbs = [
        'Everything you need to open your digital storefront and start sharing.',
        'For partners ready to build — richer microsite, wider reach, faster payouts.',
        'Scale seriously: premium presence, priority placement, deeper analytics.',
        'The flagship tier — maximum reach, concierge support, top revenue share.',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="lx">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if (! empty($settings['meta_keywords']))
        <meta name="keywords" content="{{ $settings['meta_keywords'] }}">
    @endif
    <link rel="canonical" href="{{ $pageCanonical }}">
    @if (! empty($settings['robots']))
        <meta name="robots" content="{{ $settings['robots'] }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ url('/experience') }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="{{ $settings['twitter_card'] ?? 'summary_large_image' }}">
    @if (! empty($settings['twitter_site']))
        <meta name="twitter:site" content="{{ $settings['twitter_site'] }}">
    @endif
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @if (! empty($settings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,500;1,600&display=swap" rel="stylesheet">

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

    <style>
        /* ============================================================
         * Homepage flagship design language (lx-*), scoped to this page.
         * Deliberately single-theme: a dark luxury spine interrupted by
         * two light "rooms".
         * ============================================================ */
        .lx {
            --lx-ink: #060b09;
            --lx-ink-2: #0b1310;
            --lx-ink-3: #101d17;
            --lx-emerald: #34d399;
            --lx-emerald-2: #10b981;
            --lx-emerald-deep: #065f46;
            --lx-gold: #e8c873;
            --lx-gold-2: #d4af37;
            --lx-cream: #f7f5ef;
            --lx-mist: #eaf1ec;
            --lx-text: #e9f1ec;
            --lx-muted: #9cb3a8;
            --lx-line: rgba(255, 255, 255, 0.09);
            --lx-font-head: 'Manrope', ui-sans-serif, system-ui, sans-serif;
            --lx-font-body: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            --lx-font-serif: 'Playfair Display', ui-serif, Georgia, serif;
            scroll-behavior: smooth;
        }

        .lx body,
        body.lx-body {
            background: var(--lx-ink);
            color: var(--lx-text);
            font-family: var(--lx-font-body);
            -webkit-font-smoothing: antialiased;
        }

        .lx-head { font-family: var(--lx-font-head); letter-spacing: -0.025em; }
        .lx-serif { font-family: var(--lx-font-serif); font-style: italic; font-weight: 500; }
        .lx-eyebrow {
            font-family: var(--lx-font-head);
            text-transform: uppercase;
            letter-spacing: 0.24em;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .lx-dark { background: var(--lx-ink); }
        .lx-dark-2 { background: var(--lx-ink-2); }
        .lx-light { background: var(--lx-cream); color: #12211a; }
        .lx-light .lx-muted-2 { color: #5b6b63; }

        .lx-gold-text { color: var(--lx-gold); }
        .lx-emerald-text { color: var(--lx-emerald); }
        .lx-gradient-text {
            background: linear-gradient(100deg, var(--lx-emerald) 0%, var(--lx-gold) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .lx-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid var(--lx-line);
            background: rgba(255, 255, 255, 0.03);
            border-radius: 9999px;
            padding: 0.4rem 0.9rem;
            font-size: 0.8rem;
        }

        .lx-glass {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.055), rgba(255, 255, 255, 0.015));
            border: 1px solid var(--lx-line);
            border-radius: 20px;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: transform 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .lx-glass:hover {
            transform: translateY(-6px);
            border-color: rgba(52, 211, 153, 0.35);
            box-shadow: 0 30px 60px -30px rgba(0, 0, 0, 0.7);
        }

        .lx-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border-radius: 9999px;
            font-family: var(--lx-font-head);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.9rem 1.6rem;
            transition: transform 0.2s ease, box-shadow 0.3s ease, filter 0.3s ease;
            cursor: pointer;
            white-space: nowrap;
        }
        .lx-btn:hover { transform: translateY(-2px); }
        .lx-btn-primary {
            background: linear-gradient(135deg, var(--lx-emerald) 0%, var(--lx-emerald-2) 100%);
            color: #04140d;
            box-shadow: 0 14px 40px -12px rgba(52, 211, 153, 0.6);
        }
        .lx-btn-primary:hover { box-shadow: 0 20px 50px -12px rgba(52, 211, 153, 0.75); }
        .lx-btn-gold {
            background: linear-gradient(135deg, var(--lx-gold) 0%, var(--lx-gold-2) 100%);
            color: #1c1606;
            box-shadow: 0 14px 40px -12px rgba(232, 200, 115, 0.5);
        }
        .lx-btn-ghost {
            border: 1px solid var(--lx-line);
            color: var(--lx-text);
            background: rgba(255, 255, 255, 0.02);
        }
        .lx-btn-ghost:hover { border-color: rgba(52, 211, 153, 0.4); background: rgba(52, 211, 153, 0.06); }

        .lx-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(6, 11, 9, 0.72);
            backdrop-filter: blur(14px) saturate(160%);
            -webkit-backdrop-filter: blur(14px) saturate(160%);
            border-bottom: 1px solid var(--lx-line);
        }
        .lx-navlink { color: var(--lx-muted); font-size: 0.9rem; transition: color 0.2s ease; }
        .lx-navlink:hover { color: var(--lx-text); }

        .lx-hero { position: relative; overflow: hidden; }
        .lx-aurora {
            position: absolute;
            inset: -20% -10% auto -10%;
            height: 130%;
            pointer-events: none;
            z-index: 0;
            filter: blur(60px);
            opacity: 0.55;
        }
        .lx-aurora::before,
        .lx-aurora::after {
            content: '';
            position: absolute;
            border-radius: 50%;
        }
        .lx-aurora::before {
            width: 46vw; height: 46vw;
            top: -6%; left: 8%;
            background: radial-gradient(circle at 30% 30%, rgba(52, 211, 153, 0.55), transparent 62%);
        }
        .lx-aurora::after {
            width: 40vw; height: 40vw;
            top: 8%; right: 4%;
            background: radial-gradient(circle at 60% 40%, rgba(232, 200, 115, 0.4), transparent 62%);
        }
        .lx-grid {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, #000 40%, transparent 78%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, #000 40%, transparent 78%);
        }

        .lx-url {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--lx-line);
            border-radius: 14px;
            padding: 0.7rem 1rem;
            font-family: var(--lx-font-head);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }
        .lx-url-dots { display: inline-flex; gap: 5px; }
        .lx-url-dots span { width: 9px; height: 9px; border-radius: 50%; background: rgba(255, 255, 255, 0.16); }
        .lx-livedot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--lx-emerald);
            box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.6);
        }

        .lx-frame {
            border: 1px solid var(--lx-line);
            border-radius: 22px;
            background: linear-gradient(180deg, var(--lx-ink-3), var(--lx-ink-2));
            box-shadow: 0 50px 90px -40px rgba(0, 0, 0, 0.8);
        }

        .lx-rung { position: relative; }
        .lx-rung::before {
            content: '';
            position: absolute;
            left: 27px; top: 56px; bottom: -20px;
            width: 2px;
            background: linear-gradient(var(--lx-emerald), transparent);
            opacity: 0.35;
        }
        .lx-rung:last-child::before { display: none; }

        .lx-prod {
            background: #fff;
            border: 1px solid #e7ede9;
            border-radius: 18px;
            overflow: hidden;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }
        .lx-prod:hover { transform: translateY(-6px); box-shadow: 0 30px 55px -30px rgba(6, 40, 26, 0.35); }

        .lx-wa {
            position: fixed;
            bottom: 1.25rem; right: 1.25rem;
            z-index: 45;
            width: 3.5rem; height: 3.5rem;
            border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            background: #25d366;
            color: #fff;
            box-shadow: 0 12px 30px -8px rgba(37, 211, 102, 0.6);
            transition: transform 0.2s ease;
        }
        .lx-wa:hover { transform: translateY(-3px) scale(1.05); }

        @media (prefers-reduced-motion: no-preference) {
            .lx-aurora { animation: lx-drift 18s ease-in-out infinite alternate; }
            .lx-livedot { animation: lx-ping 2s ease-out infinite; }
            .lx-shimmer { background-size: 200% auto; animation: lx-shine 6s linear infinite; }
            @keyframes lx-drift {
                from { transform: translate3d(-2%, 0, 0) scale(1); }
                to { transform: translate3d(3%, 2%, 0) scale(1.08); }
            }
            @keyframes lx-ping {
                0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.55); }
                70% { box-shadow: 0 0 0 10px rgba(52, 211, 153, 0); }
                100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
            }
            @keyframes lx-shine { to { background-position: 200% center; } }
        }

        .lx-shimmer {
            background-image: linear-gradient(100deg, var(--lx-emerald) 20%, var(--lx-gold) 40%, var(--lx-emerald) 60%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
    </style>
</head>
<body class="lx-body">
    @if (! empty($settings['gtm_id']))
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id={{ $settings['gtm_id'] }}"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    @endif

    {{-- ===================== ANNOUNCEMENT ===================== --}}
    @if (! empty($settings['announcement_text']))
        <div class="text-center fs-sm py-2 px-4" style="background: var(--lx-ink-2); color: var(--lx-text); border-bottom: 1px solid var(--lx-line);">
            @if (! empty($settings['announcement_link']))
                <a href="{{ $settings['announcement_link'] }}" class="d-inline-flex align-items-center gap-2">
                    <x-icon name="sparkles" class="w-4 h-4 lx-gold-text" />
                    {{ $settings['announcement_text'] }}
                    <span class="text-decoration-underline fw-medium lx-emerald-text">Learn more</span>
                </a>
            @else
                <span class="d-inline-flex align-items-center gap-2">
                    <x-icon name="sparkles" class="w-4 h-4 lx-gold-text" />
                    {{ $settings['announcement_text'] }}
                </span>
            @endif
        </div>
    @endif

    {{-- ============================ NAV ============================ --}}
    <header class="lx-nav">
        <div class="mw-7xl mx-auto px-6 h-16 d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="lx-head fs-xl fw-bolder tracking-tight d-flex align-items-center gap-2 text-white">
                <span class="d-inline-block w-2.5 h-2.5 rounded-pill" style="background: var(--lx-emerald); box-shadow: 0 0 12px var(--lx-emerald);"></span>
                {{ $siteName }}
            </a>
            <nav class="d-none d-md-flex align-items-center gap-9">
                <a href="#ecosystem" class="lx-navlink">Ecosystem</a>
                <a href="#storefront" class="lx-navlink">Your Storefront</a>
                <a href="#products" class="lx-navlink">Products</a>
                <a href="#tiers" class="lx-navlink">Membership</a>
                <a href="{{ route('products.index') }}" class="lx-navlink">Shop →</a>
            </nav>
            <div class="d-flex align-items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="lx-navlink d-none d-sm-inline">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="lx-navlink d-none d-sm-inline">Log in</a>
                @endauth
                <a href="{{ route('vip-plans.index') }}" class="lx-btn lx-btn-primary" style="padding:0.6rem 1.15rem; font-size:0.85rem;">Claim your link</a>
                <button type="button" class="btn btn-link p-0 text-decoration-none d-md-none text-white" data-mobile-nav-open aria-label="Open menu">
                    <x-icon name="bars" class="w-6 h-6" />
                </button>
            </div>
        </div>
        {{-- Mobile menu (toggled by app.js) --}}
        <div id="mobile-nav" class="d-none d-md-none border-top" style="border-color: var(--lx-line); background: rgba(6,11,9,0.96);">
            <nav class="px-6 py-4 d-flex flex-column gap-3">
                <a href="#ecosystem" class="lx-navlink">Ecosystem</a>
                <a href="#storefront" class="lx-navlink">Your Storefront</a>
                <a href="#products" class="lx-navlink">Products</a>
                <a href="#tiers" class="lx-navlink">Membership</a>
                <a href="{{ route('products.index') }}" class="lx-navlink">Shop</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="lx-navlink">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="lx-navlink">Log in</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- ============================ HERO ============================ --}}
    <section class="lx-hero lx-dark">
        <div class="lx-aurora"></div>
        <div class="lx-grid"></div>
        <div class="position-relative z-1 mw-7xl mx-auto px-6 pt-20 pb-24 pt-md-28 pb-md-32">
            <div class="d-grid grid-template-lg gap-14 align-items-center" style="--cols-lg: minmax(0, 1.05fr) minmax(0, 0.95fr)">
                <div>
                    <span class="lx-pill animate-fade-in-up">
                        <span class="lx-livedot"></span>
                        India's technology-first direct-selling ecosystem
                    </span>
                    <h1 class="lx-head fw-bolder text-white mt-6 lh-1 fs-5xl">
                        Your entire business,<br>
                        in one <span class="lx-serif lx-gradient-text">link</span>.
                    </h1>
                    <p class="mt-6 fs-lg lh-lg mw-xl" style="color: var(--lx-muted);">
                        Not another catalogue to memorise. The moment you join, you get a
                        premium digital storefront — products, reviews, WhatsApp, analytics —
                        live at a link that's yours to share.
                    </p>

                    <div class="mt-8 d-flex flex-wrap align-items-center gap-3">
                        <div class="lx-url">
                            <span class="lx-url-dots"><span></span><span></span><span></span></span>
                            <span class="fs-sm" style="color: var(--lx-muted);">globallife.in</span>
                            <span class="fs-sm fw-semibold text-white">/{{ $heroCity }}/{{ $heroBiz }}</span>
                            <span class="lx-livedot ms-1"></span>
                        </div>
                        @if ($heroPath)
                            <a href="{{ url($heroPath) }}" class="fs-sm lx-emerald-text fw-semibold hover-underline" style="font-family: var(--lx-font-head);">
                                See a live business →
                            </a>
                        @endif
                    </div>

                    <div class="mt-9 d-flex flex-wrap gap-3">
                        <a href="{{ route('vip-plans.index') }}" class="lx-btn lx-btn-primary">Claim your link</a>
                        <a href="#ecosystem" class="lx-btn lx-btn-ghost">How it works</a>
                    </div>

                    <div class="mt-10 d-flex flex-wrap align-items-center column-gap-7 row-gap-3 fs-sm" style="color: var(--lx-muted);">
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="shield-check" class="w-4 h-4 lx-emerald-text" /> FSSAI certified</span>
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="check-circle" class="w-4 h-4 lx-emerald-text" /> Lab tested</span>
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="map-pin" class="w-4 h-4 lx-emerald-text" /> 19+ cities</span>
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="tag" class="w-4 h-4 lx-gold-text" /> Transparent payouts</span>
                    </div>
                </div>

                <div class="reveal" data-reveal="zoom">
                    <div class="lx-frame p-3 animate-float-y">
                        <div class="rounded-4 overflow-hidden" style="background: var(--lx-ink);">
                            <div class="d-flex align-items-center gap-2 px-4 py-3 border-bottom" style="border-color: var(--lx-line);">
                                <span class="lx-url-dots"><span></span><span></span><span></span></span>
                                <div class="ms-2 flex-grow-1 fs-xs text-truncate px-3 py-1 rounded-2" style="background: rgba(255,255,255,0.05); color: var(--lx-muted);">
                                    globallife.in/{{ $heroCity }}/{{ $heroBiz }}
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-12 h-12 rounded-3 d-flex align-items-center justify-content-center lx-head fw-bolder fs-lg" style="background: linear-gradient(135deg, var(--lx-emerald), var(--lx-emerald-deep)); color:#04140d;">
                                        {{ Str::upper(Str::substr($heroMicrosite?->business_name ?? 'GL', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-white fw-semibold lx-head">{{ $heroMicrosite?->business_name ?? 'Your Business' }}</p>
                                        <p class="fs-xs" style="color: var(--lx-muted);">{{ $heroMicrosite?->city?->name ?? 'Your City' }} · Verified partner</p>
                                    </div>
                                    <span class="ms-auto lx-pill" style="padding:0.25rem 0.6rem; font-size:0.65rem;"><span class="lx-livedot"></span>Live</span>
                                </div>
                                <div class="d-grid grid-cols-3 gap-2 mt-5">
                                    @foreach (['Products', 'Reviews', 'Gallery', 'Services', 'Contact', 'FAQs'] as $mod)
                                        <div class="text-center py-2 rounded-2" style="background: rgba(255,255,255,0.04); color: var(--lx-muted);">{{ $mod }}</div>
                                    @endforeach
                                </div>
                                <div class="mt-5 rounded-3 p-4" style="background: linear-gradient(135deg, rgba(52,211,153,0.12), rgba(232,200,115,0.08)); border:1px solid var(--lx-line);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="fs-xs" style="color: var(--lx-muted);">This month</p>
                                            <p class="text-white lx-head fw-bold fs-lg">1,284 visits</p>
                                        </div>
                                        <div class="text-end">
                                            <p class="fs-xs" style="color: var(--lx-muted);">Leads</p>
                                            <p class="lx-emerald-text lx-head fw-bold fs-lg">37</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================= TRUST STRIP ======================= --}}
    <section class="lx-dark-2 border-top border-bottom" style="border-color: var(--lx-line);">
        <div class="mw-7xl mx-auto px-6 py-6 d-flex flex-wrap align-items-center justify-content-center column-gap-10 row-gap-4 fs-sm" style="color: var(--lx-muted);">
            <span class="lx-eyebrow" style="color: var(--lx-emerald);">Built on trust</span>
            <span class="d-inline-flex align-items-center gap-2"><x-icon name="shield-check" class="w-5 h-5" /> FSSAI Certified</span>
            <span class="d-inline-flex align-items-center gap-2"><x-icon name="beaker" class="w-5 h-5" /> Third-party Lab Tested</span>
            <span class="d-inline-flex align-items-center gap-2"><x-icon name="check-circle" class="w-5 h-5" /> GMP Compliant</span>
            <span class="d-inline-flex align-items-center gap-2"><x-icon name="leaf" class="w-5 h-5" /> 100% Natural</span>
            <span class="d-inline-flex align-items-center gap-2"><x-icon name="truck" class="w-5 h-5" /> Pan-India Delivery</span>
        </div>
    </section>

    {{-- ==================== THE SHIFT (light) ==================== --}}
    <section class="lx-light">
        <div class="mw-6xl mx-auto px-6 py-24 py-md-28">
            <div class="mw-2xl reveal">
                <span class="lx-eyebrow" style="color: var(--lx-emerald-2);">The shift</span>
                <h2 class="lx-head fw-bolder fs-4xl fs-5xl mt-4 lh-sm" style="color:#0f2019;">
                    Old direct selling asks you to sell first.<br>
                    <span class="lx-serif" style="color: var(--lx-gold-2);">We hand you a business first.</span>
                </h2>
            </div>

            <div class="d-grid grid-cols-md-2 gap-6 mt-14">
                <div class="reveal rounded-4 p-8 border" style="border-color:#e2e8e3; background:#fff;">
                    <p class="lx-eyebrow lx-muted-2">The traditional way</p>
                    <ul class="mt-5 d-flex flex-column gap-4">
                        @foreach (['Memorise a paper catalogue and a script', 'Chase friends and family for the first sale', 'No real presence — just your phone number', 'Commissions you can\'t see or verify'] as $pain)
                            <li class="d-flex align-items-start gap-3" style="color:#4a5a52;">
                                <x-icon name="x-mark" class="w-5 h-5 mt-1 flex-shrink-0" style="color:#c0563f;" /> {{ $pain }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="reveal rounded-4 p-8 text-white" style="transition-delay:0.1s; background: linear-gradient(160deg, var(--lx-ink-3), var(--lx-ink)); border:1px solid var(--lx-line);">
                    <p class="lx-eyebrow" style="color: var(--lx-emerald);">The Global Life way</p>
                    <ul class="mt-5 d-flex flex-column gap-4">
                        @foreach (['A live storefront at your own link, day one', 'Share one URL — products, proof, and payments in it', 'A verified presence customers can trust', 'Every rupee of commission tracked and visible'] as $win)
                            <li class="d-flex align-items-start gap-3" style="color: var(--lx-text);">
                                <x-icon name="check-circle" class="w-5 h-5 mt-1 flex-shrink-0 lx-emerald-text" /> {{ $win }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ================== THE ECOSYSTEM (dark) ================== --}}
    <section id="ecosystem" class="lx-dark">
        <div class="mw-7xl mx-auto px-6 py-24 py-md-28">
            <div class="mw-2xl reveal">
                <span class="lx-eyebrow" style="color: var(--lx-emerald);">The ecosystem</span>
                <h2 class="lx-head fw-bolder text-white fs-4xl fs-5xl mt-4 lh-sm">
                    Four roles. One transparent chain.
                </h2>
                <p class="mt-5 fs-lg" style="color: var(--lx-muted);">
                    Every level has its own dashboard, its own reach, and a clearly defined
                    share of every activation — no black boxes, no guesswork.
                </p>
            </div>

            <div class="d-grid grid-cols-lg-2 gap-14 mt-16 align-items-start">
                <div class="d-flex flex-column gap-6">
                    @foreach ([
                        ['Company', 'Sets the products, quality, and the total commission pool.', 'shield-check'],
                        ['Branch Manager', 'Runs a city. Grants each partner a share from their own cap.', 'map-pin'],
                        ['Commission Partner', 'Signs up VIP members and earns on every activation.', 'users'],
                        ['VIP Member', 'Gets the premium microsite — their business, their link.', 'sparkles'],
                    ] as $i => $role)
                        <div class="lx-rung d-flex gap-5 reveal" style="transition-delay: {{ $i * 0.08 }}s">
                            <div class="flex-shrink-0 w-14 h-14 rounded-4 d-flex align-items-center justify-content-center lx-glass" style="border-color: rgba(52,211,153,0.25);">
                                <x-icon name="{{ $role[2] }}" class="w-6 h-6 lx-emerald-text" />
                            </div>
                            <div class="pt-1">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="lx-head fs-xs fw-bold" style="color: var(--lx-gold);">0{{ $i + 1 }}</span>
                                    <h3 class="lx-head fw-bold text-white fs-lg">{{ $role[0] }}</h3>
                                </div>
                                <p class="mt-1" style="color: var(--lx-muted);">{{ $role[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="reveal lx-frame p-8" data-reveal="right">
                    <p class="lx-eyebrow" style="color: var(--lx-gold);">A single activation, split live</p>
                    <p class="lx-head text-white fs-2xl fw-bold mt-3">Every ₹ is accounted for</p>
                    <p class="fs-sm mt-2" style="color: var(--lx-muted);">Example: a Gold VIP activation, shared the instant it's confirmed.</p>

                    <div class="mt-7 d-flex flex-column gap-5">
                        @foreach ([
                            ['Commission Partner', 25, 'var(--lx-emerald)'],
                            ['Branch Manager', 5, 'var(--lx-gold)'],
                            ['Company', 70, 'rgba(255,255,255,0.35)'],
                        ] as $row)
                            <div>
                                <div class="d-flex align-items-center justify-content-between fs-sm mb-2">
                                    <span class="text-white">{{ $row[0] }}</span>
                                    <span class="lx-head fw-bold" style="color: {{ $row[2] }};">{{ $row[1] }}%</span>
                                </div>
                                <div class="h-2.5 rounded-pill overflow-hidden" style="background: rgba(255,255,255,0.06);">
                                    <div class="h-100 rounded-pill reveal" data-reveal="left" style="width: {{ $row[1] }}%; background: {{ $row[2] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-7 fs-xs" style="color: var(--lx-muted);">
                        Percentages are illustrative and set per branch. The three shares always
                        total 100% of the package — and every party sees their own record, dated and itemised.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============== YOUR DIGITAL STOREFRONT (light) ============== --}}
    <section id="storefront" class="lx-light">
        <div class="mw-7xl mx-auto px-6 py-24 py-md-28">
            <div class="d-grid grid-cols-lg-2 gap-16 align-items-center">
                <div class="reveal" data-reveal="left">
                    <span class="lx-eyebrow" style="color: var(--lx-emerald-2);">Your digital storefront</span>
                    <h2 class="lx-head fw-bolder fs-4xl fs-5xl mt-4 lh-sm" style="color:#0f2019;">
                        A whole business panel, <span class="lx-serif" style="color: var(--lx-gold-2);">toggle by toggle</span>.
                    </h2>
                    <p class="mt-5 fs-lg" style="color:#4a5a52;">
                        One universal panel powers every microsite. Switch on only the modules
                        you need — the page rearranges itself into a polished, mobile-first site.
                    </p>

                    <div class="d-grid grid-cols-sm-2 column-gap-8 row-gap-4 mt-8">
                        @foreach ([
                            ['photo', 'Profile & Banners'], ['tag', 'Products & Pricing'],
                            ['star', 'Ratings & Reviews'], ['image-stack', 'Gallery & Videos'],
                            ['chat-bubble', 'WhatsApp & Enquiries'], ['eye', 'Visitor Analytics'],
                            ['inbox', 'Lead Capture'], ['share', 'Social & Contact'],
                        ] as $mod)
                            <div class="d-flex align-items-center gap-3">
                                <span class="w-9 h-9 rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background:#e6f2eb;">
                                    <x-icon name="{{ $mod[0] }}" class="w-5 h-5" style="color: var(--lx-emerald-2);" />
                                </span>
                                <span class="fw-medium" style="color:#2b3a33;">{{ $mod[1] }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if ($heroPath)
                        <a href="{{ url($heroPath) }}" class="lx-btn lx-btn-primary mt-9">Explore a live storefront →</a>
                    @endif
                </div>

                <div class="reveal" data-reveal="right">
                    <div class="rounded-4 p-8" style="background:#fff; border:1px solid #e2e8e3; box-shadow: 0 40px 80px -40px rgba(6,40,26,0.35);">
                        <div class="d-flex align-items-center justify-content-between mb-6">
                            <p class="lx-head fw-bold" style="color:#0f2019;">Business Panel</p>
                            <span class="fs-xs px-2 py-1 rounded-pill" style="background:#e6f2eb; color: var(--lx-emerald-deep);">Dashboard</span>
                        </div>
                        @foreach ([['Products', true], ['Gallery', true], ['Reviews', true], ['Video Showcase', false], ['FAQs', true], ['Floating WhatsApp', true]] as $t)
                            <div class="d-flex align-items-center justify-content-between py-3 border-bottom" style="border-color:#eef2ef;">
                                <span class="" style="color:#2b3a33;">{{ $t[0] }}</span>
                                <span class="d-inline-flex align-items-center w-11 h-6 rounded-pill px-1 {{ $t[1] ? 'justify-content-end' : 'justify-content-start' }}" style="background: {{ $t[1] ? 'var(--lx-emerald-2)' : '#d3dad5' }};">
                                    <span class="w-5 h-5 rounded-pill bg-body shadow-sm"></span>
                                </span>
                            </div>
                        @endforeach
                        <div class="mt-6 d-grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-3 py-3" style="background:#f2f7f4;">
                                <p class="lx-head fw-bolder fs-xl" style="color:#0f2019;" data-countup="1284">1284</p>
                                <p class="" style="color:#6b7b73;">Visits</p>
                            </div>
                            <div class="rounded-3 py-3" style="background:#f2f7f4;">
                                <p class="lx-head fw-bolder fs-xl" style="color:#0f2019;" data-countup="37">37</p>
                                <p class="" style="color:#6b7b73;">Leads</p>
                            </div>
                            <div class="rounded-3 py-3" style="background:#f2f7f4;">
                                <p class="lx-head fw-bolder fs-xl" style="color:#0f2019;" data-countup="4.9">4.9</p>
                                <p class="" style="color:#6b7b73;">Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= PRODUCTS w/ PRICING (light) ================= --}}
    @if ($products->isNotEmpty())
        <section id="products" class="lx-light" style="background: var(--lx-mist);">
            <div class="mw-7xl mx-auto px-6 py-24 py-md-28">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-6 reveal">
                    <div class="mw-xl">
                        <span class="lx-eyebrow" style="color: var(--lx-emerald-2);">Real products</span>
                        <h2 class="lx-head fw-bolder fs-4xl fs-5xl mt-4 lh-sm" style="color:#0f2019;">
                            Things people actually reorder.
                        </h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="fw-semibold" style="color: var(--lx-emerald-deep); font-family: var(--lx-font-head);">View full range →</a>
                </div>

                <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6 mt-14">
                    @foreach ($products as $i => $product)
                        @php $disc = $product->discountPercentage(); @endphp
                        <a href="{{ route('products.show', $product) }}" class="lx-prod reveal d-flex flex-column" style="transition-delay: {{ $i * 0.07 }}s">
                            <div class="position-relative aspect-video overflow-hidden" style="background: linear-gradient(180deg,#fff,#eef4f0);">
                                @if ($product->main_image)
                                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-contain p-6">
                                @endif
                                @if ($disc)
                                    <span class="position-absolute top-3 end-3 fs-xs fw-bold text-white px-2 py-1 rounded-pill" style="background: var(--lx-emerald-2);">{{ $disc }}% OFF</span>
                                @endif
                            </div>
                            <div class="p-5 d-flex flex-column flex-grow-1">
                                @if ($product->badge)
                                    <span class="fw-bold text-uppercase tracking-wide mb-1" style="color: var(--lx-gold-2);">{{ $product->badge }}</span>
                                @endif
                                <h3 class="lx-head fw-bold" style="color:#0f2019;">{{ $product->name }}</h3>
                                <p class="fs-sm mt-1 line-clamp-2" style="color:#6b7b73;">{{ $product->short_description }}</p>
                                <div class="mt-auto pt-4">
                                    @if ($product->hasPrice())
                                        <div class="d-flex align-items-baseline gap-2">
                                            <span class="lx-head fs-lg fw-bolder" style="color:#0f2019;">{{ $money($product->price) }}</span>
                                            @if ($product->hasDiscount())
                                                <span class="fs-sm text-decoration-line-through" style="color:#9aa8a1;">{{ $money($product->mrp) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="fs-sm fw-semibold" style="color: var(--lx-emerald-deep);">Enquire for price</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= MEMBERSHIP TIERS (dark) ================= --}}
    @if ($plans->isNotEmpty())
        <section id="tiers" class="lx-dark">
            <div class="mw-7xl mx-auto px-6 py-24 py-md-28">
                <div class="text-center mw-2xl mx-auto reveal">
                    <span class="lx-eyebrow" style="color: var(--lx-emerald);">Membership</span>
                    <h2 class="lx-head fw-bolder text-white fs-4xl fs-5xl mt-4 lh-sm">
                        Pick the business you want to run.
                    </h2>
                    <p class="mt-5 fs-lg" style="color: var(--lx-muted);">
                        Each tier is a bigger storefront, wider reach, and a higher share.
                        One-time joining — no hidden fees.
                    </p>
                </div>

                <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-5 mt-16">
                    @foreach ($plans as $i => $plan)
                        @php $featured = $i === 1; @endphp
                        <div class="lx-glass reveal p-7 d-flex flex-column position-relative {{ $featured ? '' : '' }}"
                             style="transition-delay: {{ $i * 0.07 }}s; {{ $featured ? 'border-color: rgba(232,200,115,0.4);' : '' }}">
                            @if ($featured)
                                <span class="position-absolute top-n3 start-50 fw-bold text-uppercase tracking-wider px-3 py-1 rounded-pill" style="background: linear-gradient(135deg,var(--lx-gold),var(--lx-gold-2)); color:#1c1606;">Most popular</span>
                            @endif
                            <h3 class="lx-head fw-bold text-white fs-lg">{{ $plan->name }}</h3>
                            <div class="mt-3 d-flex align-items-baseline gap-1">
                                <span class="lx-head fw-bolder text-white fs-3xl">{{ $money($plan->joining_price) }}</span>
                                <span class="fs-xs" style="color: var(--lx-muted);">one-time</span>
                            </div>
                            <p class="mt-3 fs-sm" style="color: var(--lx-muted);">{{ $tierBlurbs[$i] ?? 'A premium digital storefront for your business.' }}</p>
                            <div class="d-flex flex-column gap-3 mt-4 mb-6 fs-sm" style="color: var(--lx-text);">
                                <p class="d-flex align-items-center gap-2"><x-icon name="check-circle" class="w-4 h-4 lx-emerald-text" /> Your own microsite link</p>
                                <p class="d-flex align-items-center gap-2"><x-icon name="check-circle" class="w-4 h-4 lx-emerald-text" /> Products, reviews & gallery</p>
                                <p class="d-flex align-items-center gap-2"><x-icon name="check-circle" class="w-4 h-4 lx-emerald-text" /> WhatsApp & lead capture</p>
                            </div>
                            <a href="{{ route('vip-plans.index') }}" class="lx-btn {{ $featured ? 'lx-btn-gold' : 'lx-btn-ghost' }} mt-auto w-100">Choose {{ $plan->name }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= STATS BAND ================= --}}
    <section class="lx-dark-2 border-top border-bottom" style="border-color: var(--lx-line);">
        <div class="mw-7xl mx-auto px-6 py-20">
            <div class="d-grid grid-cols-2 grid-cols-md-4 gap-10 text-center">
                @foreach ([['19+', 'Cities served'], ['750+', 'Active partners'], ['2.5L+', 'Happy customers'], ['₹1.2Cr+', 'Payouts disbursed']] as $stat)
                    <div class="reveal">
                        <p class="lx-head fw-bolder fs-4xl fs-5xl lx-shimmer" data-countup="{{ $stat[0] }}">{{ $stat[0] }}</p>
                        <p class="mt-2 fs-sm" style="color: var(--lx-muted);">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== CLOSING CTA ===================== --}}
    <section class="lx-dark position-relative overflow-hidden">
        <div class="lx-aurora" style="opacity:0.4;"></div>
        <div class="position-relative z-1 mw-4xl mx-auto px-6 py-28 text-center">
            <h2 class="lx-head fw-bolder text-white fs-4xl fs-5xl lh-1 reveal">
                Your link is <span class="lx-gradient-text">waiting</span>.
            </h2>
            <p class="mt-6 fs-lg mw-xl mx-auto reveal" style="color: var(--lx-muted);">
                Join Global Life and walk away with a live digital business — not a pitch,
                not a promise. A real storefront you can share tonight.
            </p>
            <div class="mt-10 d-flex flex-wrap justify-content-center gap-4 reveal">
                <a href="{{ route('vip-plans.index') }}" class="lx-btn lx-btn-primary" style="padding:1.05rem 2rem;">Claim your link</a>
                <a href="{{ route('contact') }}" class="lx-btn lx-btn-ghost" style="padding:1.05rem 2rem;">
                    <x-icon name="chat-bubble" class="w-5 h-5" /> Talk to us
                </a>
            </div>
        </div>
    </section>

    {{-- ======================= FOOTER ======================= --}}
    <footer class="lx-dark border-top" style="border-color: var(--lx-line);">
        <div class="mw-7xl mx-auto px-6 py-16">
            <div class="d-grid grid-template-md gap-10" style="--cols-md: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr)">
                <div>
                    <a href="{{ url('/') }}" class="lx-head fs-xl fw-bolder text-white d-flex align-items-center gap-2">
                        <span class="d-inline-block w-2.5 h-2.5 rounded-pill" style="background: var(--lx-emerald); box-shadow:0 0 12px var(--lx-emerald);"></span>
                        {{ $siteName }}
                    </a>
                    <p class="mt-4 fs-sm mw-xs" style="color: var(--lx-muted);">
                        A technology-first direct-selling company. Real products, real presence,
                        transparent revenue — one link at a time.
                    </p>
                    <div class="d-flex align-items-center gap-3 mt-5">
                        @foreach (['social_facebook' => 'Facebook', 'social_instagram' => 'Instagram', 'social_youtube' => 'YouTube', 'social_linkedin' => 'LinkedIn'] as $key => $label)
                            @if (! empty($settings[$key]))
                                <a href="{{ $settings[$key] }}" target="_blank" rel="noopener" class="lx-navlink fs-xs">{{ $label }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @foreach ([
                    'Explore' => [['Products', route('products.index')], ['VIP Plans', route('vip-plans.index')], ['Blog', route('blog.index')], ['Events', route('events.index')]],
                    'Company' => [['Contact', route('contact')], ['Log in', route('login')]],
                    'Legal' => [['Privacy', url('/')], ['Terms', url('/')]],
                ] as $col => $links)
                    <div>
                        <p class="lx-eyebrow" style="color: var(--lx-muted);">{{ $col }}</p>
                        <ul class="mt-4 d-flex flex-column gap-3 fs-sm">
                            @foreach ($links as $link)
                                <li><a href="{{ $link[1] }}" class="lx-navlink">{{ $link[0] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <div class="mt-14 pt-6 border-top d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 fs-xs" style="border-color: var(--lx-line); color: var(--lx-muted);">
                <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                <p>Made in India 🇮🇳</p>
            </div>
        </div>
    </footer>

    @if ($whatsapp)
        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="lx-wa" aria-label="Chat on WhatsApp">
            <x-icon name="chat-bubble" class="w-7 h-7" />
        </a>
    @endif
</body>
</html>
