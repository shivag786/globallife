@php
    use Illuminate\Support\Str;

    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? 'Global Life';
    $money = fn ($n) => '₹'.number_format((float) $n, 0);
    $u = fn ($id, $w = 1200) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=70";
    $demoUrl = $heroMicrosite ? url($heroMicrosite->publicPath()) : route('vip-plans.index');

    $img = [
        'hero'      => $u('1600880292203-757bb62b4baf', 1400),
        'seo'       => $u('1573497019940-1c28c88b4f3e', 1100),
        'retail'    => $u('1556742049-0cfed4f6a45d', 1100),
        'trust'     => $u('1511895426328-dc8714191300', 1100),
        'phone'     => $u('1512941937669-90a1b58e7e9c', 800),
        'cta'       => $u('1521737604893-d14cc237f11d', 1500),
    ];
    // Microsite demo tabs
    $mockups = [
        ['Doctor', $u('1612349317150-e413f6a5b16d', 900), 'Dr. Verma Clinic', 'Family Physician'],
        ['Gym', $u('1534438327276-14e5300c3a48', 900), 'IronCore Fitness', 'Strength & Cardio'],
        ['Salon', $u('1560066984-138dadb4c035', 900), 'Glow Studio', 'Hair · Skin · Spa'],
        ['Restaurant', $u('1517248135467-4c7edcad34c4', 900), 'Spice Route', 'Fine Dining'],
        ['Retail', $u('1441986300917-64674bd600d8', 900), 'Daily Mart', 'Neighbourhood Store'],
    ];
    $mockData = array_map(fn ($m) => [
        'img' => $m[1], 'name' => $m[2], 'cat' => $m[3],
        'slug' => Str::slug($m[2]), 'ini' => Str::upper(Str::substr($m[2], 0, 2)),
    ], $mockups);
    $stories = [
        ['Gym Owner', $u('1534438327276-14e5300c3a48', 900), 'No online presence — relied only on walk-ins.', 'A professional microsite with class schedules, reviews and product sales.', ['+180%','Enquiries'],['3.2x','Repeat customers']],
        ['Doctor', $u('1612349317150-e413f6a5b16d', 900), 'Patients came only through word-of-mouth referrals.', 'Online appointments, verified reviews, Google visibility and wellness recommendations.', ['+240','Monthly appointments'],['4.9★','Patient rating']],
        ['Salon', $u('1560066984-138dadb4c035', 900), 'Limited to local customers who happened to pass by.', 'A gallery, WhatsApp bookings, reviews and add-on perfume sales.', ['+320%','WhatsApp bookings'],['2.1x','Average order value']],
    ];
    $categories = [
        ['Doctor','1612349317150-e413f6a5b16d','Appointments, reviews & visibility'],
        ['Salon','1560066984-138dadb4c035','Gallery, bookings & add-on sales'],
        ['Gym','1534438327276-14e5300c3a48','Schedules, leads & supplements'],
        ['Restaurant','1517248135467-4c7edcad34c4','Menu, reviews & reservations'],
        ['Retail Shop','1441986300917-64674bd600d8','Product showcase & online orders'],
        ['Manufacturer','1581091226825-a6a2a5aee158','Catalog, enquiries & B2B leads'],
        ['Freelancer','1499750310107-5fef28a66643','Portfolio, reviews & contact'],
        ['Consultant','1573497019940-1c28c88b4f3e','Services, booking & authority'],
        ['Teacher','1509062522246-3755977927d7','Courses, batches & enrolment'],
        ['Lawyer','1589829545856-d10d557cf95f','Practice areas & consultations'],
        ['CA / Finance','1554224155-6726b3ff858f','Services, trust & enquiries'],
        ['Influencer','1524504388940-b1c1722653e1','Link-in-bio store & deals'],
        ['Student Founder','1523240795612-9a054b0db644','Launch fast, sell online'],
        ['Home Entrepreneur','1556911220-bff31c812dba','Turn a passion into income'],
    ];
    $schema = [
        '@context' => 'https://schema.org', '@type' => 'Service',
        'name' => $siteName.' Business Growth Platform',
        'description' => 'A complete digital business platform: microsite, SEO, product selling, reviews, WhatsApp leads, analytics and referral income.',
        'url' => url('/fourth'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg4">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} — Everything Your Business Needs to Grow, in One Platform</title>
    <meta name="description" content="A complete digital business growth platform for every business — a professional microsite, SEO & Google visibility, product selling, reviews, WhatsApp leads, analytics, and referral income.">
    <link rel="canonical" href="{{ url('/fourth') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $siteName }} — Business Growth Platform">
    <meta property="og:description" content="Build your digital identity, reach more customers, and grow online — all from one professional platform.">
    <meta property="og:url" content="{{ url('/fourth') }}">
    <meta property="og:image" content="{{ $img['hero'] }}">
    <meta name="twitter:card" content="summary_large_image">
    @if (! empty($settings['favicon']))<link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">@endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES) !!}</script>

    <style>
        .bg4 {
            --i:#14231b; --mut:#5c6b63; --line:#e7ede9; --cream:#f7f5ef; --mist:#eef4f0;
            --b:#245a3f; --b6:#2c704c; --b7:#1f4834; --b9:#14231b; --gold:#d4af37;
            --hd:'Poppins','Manrope',ui-sans-serif,system-ui,sans-serif; scroll-behavior:smooth;
        }
        .bg4 body, body.bg4-body { background:#fff; color:var(--i); font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif; -webkit-font-smoothing:antialiased; }
        .bg4-hd { font-family:var(--hd); letter-spacing:-.02em; }
        .bg4-eyebrow { font-family:var(--hd); text-transform:uppercase; letter-spacing:.2em; font-size:.72rem; font-weight:700; color:var(--b6); }
        .bg4-mut { color:var(--mut); }
        .bg4-img { position:relative; overflow:hidden; background:linear-gradient(135deg,#dfe9e3,#cfe0d5); }
        .bg4-img > img { width:100%; height:100%; object-fit:cover; display:block; }

        .bg4-header { position:sticky; top:0; z-index:50; transition:background .3s,box-shadow .3s,border-color .3s; border-bottom:1px solid transparent; }
        .bg4-header.is-scrolled { background:rgba(255,255,255,.82); backdrop-filter:blur(14px) saturate(160%); -webkit-backdrop-filter:blur(14px) saturate(160%); border-bottom-color:var(--line); box-shadow:0 6px 24px -18px rgba(0,0,0,.3); }
        .bg4-navlink { font-size:.9rem; font-weight:500; color:var(--i); transition:color .2s; }
        .bg4-navlink:hover { color:var(--b6); }

        .bg4-btn { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; border-radius:9999px; font-family:var(--hd); font-weight:600; font-size:.95rem; padding:.85rem 1.6rem; transition:transform .2s,box-shadow .3s,background .3s; cursor:pointer; white-space:nowrap; }
        .bg4-btn:hover { transform:translateY(-2px); }
        .bg4-btn-primary { background:linear-gradient(135deg,var(--b6),var(--b7)); color:#fff; box-shadow:0 14px 34px -12px rgba(36,90,63,.6); }
        .bg4-btn-primary:hover { box-shadow:0 20px 44px -12px rgba(36,90,63,.75); }
        .bg4-btn-gold { background:linear-gradient(135deg,#e8c873,var(--gold)); color:#1c1606; box-shadow:0 14px 34px -12px rgba(212,175,55,.5); }
        .bg4-btn-light { background:rgba(255,255,255,.14); color:#fff; border:1px solid rgba(255,255,255,.6); backdrop-filter:blur(6px); }
        .bg4-btn-light:hover { background:rgba(255,255,255,.24); }
        .bg4-btn-ghost { border:1px solid var(--line); color:var(--b7); background:#fff; }
        .bg4-btn-ghost:hover { border-color:var(--b); background:var(--mist); }

        .bg4-card { background:#fff; border:1px solid var(--line); border-radius:20px; transition:transform .35s,box-shadow .35s,border-color .35s; }
        .bg4-card:hover { transform:translateY(-6px); border-color:#cfe0d5; box-shadow:0 30px 55px -30px rgba(6,40,26,.32); }
        .bg4-feat-icon { transition:box-shadow .35s, transform .35s; }
        .bg4-card:hover .bg4-feat-icon { box-shadow:0 0 0 6px rgba(36,90,63,.08); transform:scale(1.05); }

        /* Mesh gradient */
        .bg4-mesh { position:absolute; inset:0; z-index:0; overflow:hidden; }
        .bg4-mesh::before, .bg4-mesh::after { content:''; position:absolute; border-radius:50%; filter:blur(70px); opacity:.55; }
        .bg4-mesh::before { width:48vw; height:48vw; top:-12%; left:-6%; background:radial-gradient(circle,rgba(52,211,153,.5),transparent 62%); }
        .bg4-mesh::after { width:42vw; height:42vw; bottom:-14%; right:-4%; background:radial-gradient(circle,rgba(232,200,115,.42),transparent 62%); }

        .bg4-cat { position:relative; border-radius:18px; overflow:hidden; display:block; }
        .bg4-cat img { transition:transform .7s cubic-bezier(.2,.7,.2,1); }
        .bg4-cat:hover img { transform:scale(1.09); }
        .bg4-cat::after { content:''; position:absolute; inset:0; background:linear-gradient(to top,rgba(15,32,25,.9) 6%,rgba(15,32,25,.15) 55%,transparent); }

        /* Browser + phone mockups */
        .bg4-browser { border-radius:16px; overflow:hidden; box-shadow:0 40px 80px -40px rgba(6,40,26,.4); border:1px solid var(--line); background:#fff; }
        .bg4-phone { width:190px; border-radius:30px; border:8px solid #10201a; overflow:hidden; box-shadow:0 40px 70px -30px rgba(0,0,0,.5); background:#000; }

        .bg4-tab { font-size:.85rem; font-weight:600; padding:.5rem 1rem; border-radius:9999px; color:var(--mut); transition:all .25s; border:1px solid var(--line); background:#fff; }
        .bg4-tab.is-active { background:var(--b7); color:#fff; border-color:var(--b7); }

        /* SVG path draw */
        [data-draw] { stroke-dasharray:1; stroke-dashoffset:1; transition:none; }
        [data-draw].is-drawn { animation:bg4-draw 1.6s ease forwards; }
        @keyframes bg4-draw { to { stroke-dashoffset:0; } }

        /* Chart bars */
        .bg4-bar { height:0; transition:height 1s cubic-bezier(.2,.7,.2,1); }
        .is-charted .bg4-bar { height:var(--h); }

        /* Comparison rows */
        .bg4-row { transition:background .25s; }
        .bg4-row:hover { background:var(--mist); }

        /* WhatsApp bubbles */
        @media (prefers-reduced-motion: no-preference) {
            .bg4-float { animation:bg4-float 5s ease-in-out infinite; }
            .bg4-float.d1 { animation-delay:.9s; } .bg4-float.d2 { animation-delay:1.6s; }
            @keyframes bg4-float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        }
        #bg4-scarousel { scrollbar-width:none; } #bg4-scarousel::-webkit-scrollbar { display:none; }
        .bg4-dot { width:8px; height:8px; border-radius:9999px; background:rgba(20,35,27,.2); transition:width .3s,background .3s; }
        .bg4-dot.is-active { width:22px; background:var(--b); }
        .bg4-underline { position:relative; }
        .bg4-underline::after { content:''; position:absolute; left:0; right:0; bottom:.06em; height:.32em; background:rgba(212,175,55,.35); z-index:-1; border-radius:2px; }
    </style>
</head>
<body class="bg4-body">

    {{-- ===== HEADER ===== --}}
    <header class="bg4-header" id="bg4-header">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 flex items-center justify-between" style="height:4.5rem;">
            <a href="{{ url('/') }}" class="bg4-hd text-xl font-extrabold text-brand-800 flex items-center gap-2">
                @if (! empty($settings['site_logo']))<img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}" class="h-9 w-auto">
                @else<span class="w-2.5 h-2.5 rounded-full" style="background:var(--b);box-shadow:0 0 10px var(--b);"></span> {{ $siteName }}@endif
            </a>
            <nav class="hidden lg:flex items-center gap-8">
                <a href="#features" class="bg4-navlink">Features</a>
                <a href="#microsite" class="bg4-navlink">Microsite</a>
                <a href="#seo" class="bg4-navlink">SEO</a>
                <a href="#analytics" class="bg4-navlink">Analytics</a>
                <a href="#stories" class="bg4-navlink">Success</a>
            </nav>
            <div class="flex items-center gap-3 sm:gap-5">
                <a href="{{ route('login') }}" class="bg4-navlink hidden sm:inline">Log in</a>
                <a href="{{ route('vip-plans.index') }}" class="bg4-btn bg4-btn-primary" style="padding:.6rem 1.15rem;font-size:.85rem;">Create Business Page</a>
            </div>
        </div>
    </header>

    {{-- ===== SECTION 1 — EVERYTHING YOUR BUSINESS NEEDS ===== --}}
    <section id="features" class="relative overflow-hidden">
        <div class="bg4-mesh"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 pt-20 md:pt-28 pb-16">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="bg4-eyebrow">Business Growth Platform</span>
                    <h1 class="bg4-hd font-extrabold leading-[1.05] mt-4 text-4xl md:text-5xl lg:text-[3.4rem]">Everything your business needs to <span class="bg4-underline">grow</span>, in one platform</h1>
                    <p class="mt-6 text-lg bg4-mut max-w-xl">Build your digital identity, reach more customers, collect enquiries, showcase products and services, improve your Google visibility, and unlock new earning opportunities — all from a single professional platform.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('vip-plans.index') }}" class="bg4-btn bg4-btn-primary">Create My Business Page</a>
                        <a href="{{ $demoUrl }}" target="_blank" class="bg4-btn bg4-btn-ghost"><x-icon name="play" class="w-4 h-4" /> View Live Demo</a>
                    </div>
                </div>
                <div class="bg4-img reveal rounded-[26px] aspect-[4/3] shadow-2xl" data-reveal="zoom">
                    <img src="{{ $img['hero'] }}" alt="Indian entrepreneur growing their business" onerror="this.style.display='none'" fetchpriority="high">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-16">
                @foreach ([
                    ['photo','Professional Microsite','A polished business page in minutes.'],
                    ['tag','Products &amp; Services','Sell and showcase, beautifully.'],
                    ['eye','SEO Optimization','Be found on Google search.'],
                    ['star','Customer Reviews','Build trust that converts.'],
                    ['chat-bubble','WhatsApp &amp; Contact','One tap to reach you.'],
                    ['calendar','Appointment &amp; Enquiry','Capture every lead.'],
                    ['image-stack','Analytics Dashboard','See what is working.'],
                    ['users','Referral Income','Earn as you grow.'],
                ] as $i => $f)
                    <div class="bg4-card reveal p-6" style="transition-delay: {{ ($i % 4) * 0.07 }}s">
                        <span class="bg4-feat-icon w-12 h-12 rounded-xl flex items-center justify-center" style="background:#e6f2eb;"><x-icon name="{{ $f[0] }}" class="w-6 h-6 text-brand-600" /></span>
                        <h3 class="bg4-hd font-semibold text-brand-900 mt-4">{!! $f[1] !!}</h3>
                        <p class="bg4-mut text-sm mt-1">{{ $f[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== SECTION 2 — YOUR BUSINESS MICROSITE ===== --}}
    <section id="microsite" class="py-20 md:py-28" style="background:var(--cream);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="grid lg:grid-cols-2 gap-14 items-center">
                <div class="reveal" data-reveal="left">
                    <span class="bg4-eyebrow">Your business microsite</span>
                    <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">A modern website for your business — done for you</h2>
                    <p class="mt-5 bg4-mut text-lg">Every VIP member receives a professional microsite with everything customers expect.</p>
                    <div class="flex flex-wrap gap-2 mt-6">
                        @foreach (['About','Products','Services','Gallery','Videos','Reviews','Contact','WhatsApp','Booking','Google Map','SEO Pages'] as $m)
                            <span class="text-sm px-3 py-1.5 rounded-full bg-white border" style="border-color:var(--line);color:var(--mut);">{{ $m }}</span>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2 mt-8" id="bg4-tabs">
                        @foreach ($mockups as $i => $mk)
                            <button type="button" class="bg4-tab {{ $i === 0 ? 'is-active' : '' }}" data-tab="{{ $i }}">{{ $mk[0] }}</button>
                        @endforeach
                    </div>
                    <a href="{{ $demoUrl }}" target="_blank" class="bg4-btn bg4-btn-primary mt-8">View Live Demo</a>
                </div>

                {{-- Mockups --}}
                <div class="reveal relative" data-reveal="right">
                    <div class="bg4-browser">
                        <div class="flex items-center gap-1.5 px-4 py-2.5 border-b" style="border-color:var(--line);background:#f6f8f7;">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                            <span class="ml-3 text-xs text-slate-400 truncate">globallife.in/<span id="bg4-mock-slug">dr-verma</span></span>
                        </div>
                        <div class="bg4-img aspect-[16/10]"><img id="bg4-mock-img" src="{{ $mockups[0][1] }}" alt="Business microsite preview" loading="lazy" onerror="this.style.display='none'"></div>
                        <div class="p-4 flex items-center gap-3 bg-white">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold bg4-hd" style="background:var(--b6);"><span id="bg4-mock-ini">DR</span></div>
                            <div><p class="bg4-hd font-bold text-brand-900 text-sm" id="bg4-mock-name">{{ $mockups[0][2] }}</p><p class="text-xs bg4-mut" id="bg4-mock-cat">{{ $mockups[0][3] }}</p></div>
                            <span class="ml-auto text-[0.6rem] font-semibold text-green-700 bg-green-50 px-2 py-1 rounded-full">● Live</span>
                        </div>
                    </div>
                    <div class="bg4-phone absolute -bottom-8 -left-4 hidden md:block bg4-float">
                        <div class="bg4-img aspect-[9/17]"><img src="{{ $mockups[0][1] }}" alt="Mobile preview" loading="lazy" onerror="this.style.display='none'" id="bg4-mock-phone"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 3 — GOOGLE VISIBILITY & SEO ===== --}}
    <section id="seo" class="py-20 md:py-28">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="grid lg:grid-cols-2 gap-14 items-center">
                <div class="reveal" data-reveal="left">
                    <span class="bg4-eyebrow">Google visibility &amp; SEO</span>
                    <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">Be found before your competitors</h2>
                    <p class="mt-5 bg4-mut text-lg">Customers search online before they buy. If your business doesn't show up with complete information, they choose someone who does.</p>
                    {{-- animated search bar --}}
                    <div class="mt-7 bg-white border rounded-full flex items-center gap-3 px-5 py-3.5 shadow-sm" style="border-color:var(--line);">
                        <x-icon name="eye" class="w-5 h-5 text-slate-400" />
                        <span id="bg4-type" class="text-brand-900 font-medium"></span><span class="w-0.5 h-5 bg-brand-600 animate-pulse"></span>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 mt-7">
                        @foreach (['SEO-friendly pages','Fast loading','Schema markup','Blog support','Local SEO','Business keywords','Google indexing','Rich snippets'] as $s)
                            <span class="flex items-center gap-2 text-sm text-brand-900/80"><x-icon name="check-circle" class="w-4 h-4 text-brand-600" /> {{ $s }}</span>
                        @endforeach
                    </div>
                </div>
                {{-- journey flow --}}
                <div class="reveal" data-reveal="right">
                    <div class="bg-white rounded-[26px] border p-8" style="border-color:var(--line);box-shadow:0 40px 80px -50px rgba(6,40,26,.35);">
                        <svg viewBox="0 0 60 320" class="hidden" aria-hidden="true"></svg>
                        <div class="space-y-3">
                            @foreach ([['eye','Google Search','A customer searches for your service'],['photo','Your Business Profile','Your microsite appears, complete & trusted'],['users','Customer Visits','They explore products, reviews & gallery'],['chat-bubble','Contact','One tap to WhatsApp, call or enquire'],['tag','Purchase','They buy — and often come back']] as $k => $step)
                                <div class="flex items-center gap-4 reveal" style="transition-delay: {{ $k * 0.08 }}s">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#e6f2eb;"><x-icon name="{{ $step[0] }}" class="w-5 h-5 text-brand-600" /></div>
                                    <div class="flex-1"><p class="bg4-hd font-semibold text-brand-900">{{ $step[1] }}</p><p class="text-xs bg4-mut">{{ $step[2] }}</p></div>
                                    <span class="bg4-hd text-xs font-bold text-slate-300">0{{ $k + 1 }}</span>
                                </div>
                                @if (! $loop->last)<div class="ml-5 h-4 w-px" style="background:linear-gradient(var(--b),transparent);"></div>@endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 4 — SHOWCASE PRODUCTS & SERVICES ===== --}}
    <section class="py-20 md:py-28" style="background:var(--mist);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-14 items-center">
            <div class="bg4-img reveal rounded-[26px] aspect-[4/5] shadow-xl order-2 lg:order-1" data-reveal="left">
                <img src="{{ $img['retail'] }}" alt="Retail owner arranging products" loading="lazy" onerror="this.style.display='none'">
            </div>
            <div class="order-1 lg:order-2 reveal" data-reveal="right">
                <span class="bg4-eyebrow">Showcase &amp; sell</span>
                <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">Show your products &amp; take orders online</h2>
                <p class="mt-5 bg4-mut text-lg">A premium storefront built into your microsite — ratings, offers, cart, and booking included.</p>
                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4 mt-8">
                        @foreach ($products->take(2) as $product)
                            <div class="bg4-card reveal overflow-hidden">
                                <div class="relative aspect-square flex items-center justify-center" style="background:linear-gradient(180deg,#fff,#eef4f0);">
                                    @if ($product->main_image)<img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-5" loading="lazy">@endif
                                    @if ($product->discountPercentage())<span class="absolute top-3 left-3 text-xs font-bold text-white px-2 py-1 rounded-full bg-green-600">{{ $product->discountPercentage() }}% OFF</span>@endif
                                </div>
                                <div class="p-4">
                                    <p class="bg4-hd font-semibold text-brand-900 text-sm line-clamp-1">{{ $product->name }}</p>
                                    @if ($product->review_count > 0)<div class="flex items-center gap-1 mt-1"><x-icon name="star" class="w-3.5 h-3.5 text-gold-500" :filled="true" /><span class="text-xs bg4-mut">{{ number_format((float)$product->rating,1) }} ({{ $product->review_count }})</span></div>@endif
                                    <div class="flex items-baseline gap-1.5 mt-1.5">
                                        @if ($product->hasPrice())<span class="font-bold text-brand-900">{{ $money($product->sellingPrice()) }}</span>@if ($product->hasDiscount())<span class="text-xs bg4-mut line-through">{{ $money($product->mrp) }}</span>@endif @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="flex flex-wrap gap-2 mt-6">
                    @foreach (['Add to Cart','Buy Now','Service Booking','Offer Price','Discount Badge','Categories'] as $t)
                        <span class="text-xs px-3 py-1.5 rounded-full bg-white border" style="border-color:var(--line);color:var(--mut);">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 5 — BUILD CUSTOMER TRUST ===== --}}
    <section class="py-20 md:py-28">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-14 items-center">
            <div class="reveal" data-reveal="left">
                <span class="bg4-eyebrow">Build customer trust</span>
                <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">People trust businesses they can see</h2>
                <p class="mt-5 bg4-mut text-lg">Reviews, ratings, certificates and testimonials — the signals that turn a visitor into a customer.</p>
                <div class="grid sm:grid-cols-2 gap-4 mt-8">
                    @foreach ([['star','Customer Reviews'],['check-circle','Google Ratings'],['shield-check','Verified Badge'],['play','Video Testimonials'],['academic-cap','Business Certificates'],['calendar','Years of Experience']] as $tr)
                        <div class="flex items-center gap-3"><span class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#e6f2eb;"><x-icon name="{{ $tr[0] }}" class="w-5 h-5 text-brand-600" /></span><span class="text-sm font-medium text-brand-900">{{ $tr[1] }}</span></div>
                    @endforeach
                </div>
                <div class="grid grid-cols-3 gap-4 mt-9">
                    <div class="text-center"><p class="bg4-hd font-extrabold text-3xl text-brand-800" data-countup="4.9">4.9</p><p class="text-xs bg4-mut">Avg Rating</p></div>
                    <div class="text-center"><p class="bg4-hd font-extrabold text-3xl text-brand-800" data-countup="10,000+">10,000+</p><p class="text-xs bg4-mut">Happy Customers</p></div>
                    <div class="text-center"><p class="bg4-hd font-extrabold text-3xl text-brand-800" data-countup="100%">100%</p><p class="text-xs bg4-mut">Verified Reviews</p></div>
                </div>
            </div>
            <div class="bg4-img reveal rounded-[28px] aspect-[4/5] shadow-xl" data-reveal="right">
                <img src="{{ $img['trust'] }}" alt="Happy customers" loading="lazy" onerror="this.style.display='none'">
            </div>
        </div>
    </section>

    {{-- ===== SECTION 6 — WHATSAPP, CALLS & LEADS ===== --}}
    <section class="py-20 md:py-28 relative overflow-hidden" style="background:var(--b9);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-14 items-center">
            <div class="text-white reveal" data-reveal="left">
                <span class="bg4-eyebrow" style="color:#e8c873;">WhatsApp, calls &amp; leads</span>
                <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">Turn visitors into conversations</h2>
                <p class="mt-5 text-white/80 text-lg">Every enquiry lands where you already are — one tap to WhatsApp, call, or book.</p>
                <div class="space-y-3 mt-8">
                    @foreach (['Customer clicks WhatsApp','Business receives the message','Business replies instantly','Customer books an appointment','Sale completed'] as $k => $flow)
                        <div class="flex items-center gap-3"><span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold" style="background:rgba(232,200,115,.16);color:#e8c873;">{{ $k + 1 }}</span><span class="text-white/90 text-[0.95rem]">{{ $flow }}</span></div>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-2 mt-8">
                    @foreach (['Click to Call','WhatsApp','Contact Form','Appointment Booking','Google Maps','Business Hours'] as $c)
                        <span class="text-xs px-3 py-1.5 rounded-full text-white/85" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);">{{ $c }}</span>
                    @endforeach
                </div>
            </div>
            {{-- phone with floating bubbles --}}
            <div class="reveal relative flex justify-center" data-reveal="right">
                <div class="bg4-phone" style="width:230px;">
                    <div class="bg4-img aspect-[9/18]"><img src="{{ $img['phone'] }}" alt="Customer messaging a business" loading="lazy" onerror="this.style.display='none'"></div>
                </div>
                <div class="bg4-float absolute top-8 -left-2 sm:left-4 bg-white rounded-2xl rounded-bl-sm shadow-xl px-4 py-2.5 text-sm max-w-[200px]">Hi! Is this available today? <span class="block text-[0.6rem] text-slate-400 mt-0.5">Customer · 10:24</span></div>
                <div class="bg4-float d1 absolute top-28 -right-2 sm:right-2 rounded-2xl rounded-br-sm shadow-xl px-4 py-2.5 text-sm max-w-[210px] text-white" style="background:#25d366;">Yes! Want me to book you a slot? <span class="block text-[0.6rem] text-white/70 mt-0.5">Business · 10:24</span></div>
                <div class="bg4-float d2 absolute bottom-10 -left-2 sm:left-2 bg-white rounded-2xl rounded-bl-sm shadow-xl px-4 py-2.5 text-sm max-w-[200px]">Perfect, please book 🙏 <span class="block text-[0.6rem] text-slate-400 mt-0.5">Customer · 10:25</span></div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 7 — ANALYTICS DASHBOARD ===== --}}
    <section id="analytics" class="py-20 md:py-28" style="background:var(--mist);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="text-center max-w-2xl mx-auto reveal">
                <span class="bg4-eyebrow">Business analytics</span>
                <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">Know exactly what's working</h2>
            </div>
            <div class="reveal bg-white rounded-[26px] border p-6 md:p-8 mt-12" data-reveal="zoom" id="bg4-dash" style="border-color:var(--line);box-shadow:0 50px 90px -50px rgba(6,40,26,.4);">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ([['Visitors','1,284'],['Profile Views','942'],['Enquiries','37'],['WhatsApp Clicks','156'],['Calls','83'],['Orders','48'],['Revenue','₹1,86,400'],['Commission','₹12,320']] as $m)
                        <div class="rounded-xl p-4" style="background:var(--mist);">
                            <p class="text-xs bg4-mut">{{ $m[0] }}</p>
                            <p class="bg4-hd font-extrabold text-xl md:text-2xl text-brand-900 mt-1" data-countup="{{ $m[1] }}">{{ $m[1] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="grid md:grid-cols-3 gap-6 mt-6">
                    <div class="md:col-span-2 rounded-xl border p-5" style="border-color:var(--line);">
                        <p class="bg4-hd font-semibold text-brand-900 text-sm mb-4">Monthly Growth</p>
                        <div class="flex items-end gap-2 h-40">
                            @foreach ([38,52,45,63,58,72,68,84,79,92,88,100] as $h)
                                <div class="flex-1 rounded-t-md bg4-bar" style="--h:{{ $h }}%;background:linear-gradient(var(--b6),var(--b7));"></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-xl border p-5" style="border-color:var(--line);">
                        <p class="bg4-hd font-semibold text-brand-900 text-sm mb-4">Top Products</p>
                        <div class="space-y-3">
                            @foreach ([['Family Prowell',82],['Vitality Herbal',64],['Jasmine EDP',47]] as $tp)
                                <div><div class="flex justify-between text-xs mb-1"><span class="text-brand-900/80">{{ $tp[0] }}</span><span class="bg4-mut">{{ $tp[1] }}%</span></div><div class="h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg4-bar" style="--h:100%;width:{{ $tp[1] }}%;background:var(--b);height:100%;"></div></div></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 8 — GROWTH JOURNEY (SVG timeline) ===== --}}
    <section class="py-20 md:py-28">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="text-center max-w-2xl mx-auto reveal">
                <span class="bg4-eyebrow">Your growth journey</span>
                <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">From zero to selling online — in 8 steps</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-14">
                @foreach ([
                    'Create Business Profile','Complete Your Information','Publish Your Microsite','Appear on Google',
                    'Share Your Business Link','Receive Customer Enquiries','Sell Products &amp; Services','Earn Referral Commission',
                ] as $i => $step)
                    <div class="reveal relative" style="transition-delay: {{ ($i % 4) * 0.08 }}s">
                        <div class="bg4-card p-6 h-full">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg4-hd font-extrabold text-white text-lg" style="background:linear-gradient(135deg,var(--b6),var(--b7));">{{ $i + 1 }}</div>
                            <p class="bg4-hd font-semibold text-brand-900 mt-4">{!! $step !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== SECTION 9 — SUCCESS STORIES (carousel) ===== --}}
    <section id="stories" class="py-20 md:py-28" style="background:var(--cream);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 reveal">
                <div><span class="bg4-eyebrow">Real success stories</span><h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3">Before &amp; after</h2></div>
                <div class="flex gap-2">
                    <button type="button" data-scar-prev class="w-10 h-10 rounded-full border bg-white flex items-center justify-center text-brand-700 hover:bg-slate-50 transition" style="border-color:var(--line);"><x-icon name="chevron-left" class="w-5 h-5" /></button>
                    <button type="button" data-scar-next class="w-10 h-10 rounded-full border bg-white flex items-center justify-center text-brand-700 hover:bg-slate-50 transition" style="border-color:var(--line);"><x-icon name="chevron-right" class="w-5 h-5" /></button>
                </div>
            </div>
            <div id="bg4-scarousel" class="flex overflow-x-auto snap-x snap-mandatory gap-6 mt-10 pb-2">
                @foreach ($stories as $s)
                    <article class="snap-center shrink-0 w-full md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)] bg-white rounded-[24px] border overflow-hidden" style="border-color:var(--line);">
                        <div class="bg4-img aspect-[16/10]"><img src="{{ $s[1] }}" alt="{{ $s[0] }}" loading="lazy" onerror="this.style.display='none'"></div>
                        <div class="p-6">
                            <span class="bg4-eyebrow">{{ $s[0] }}</span>
                            <div class="mt-3 space-y-2 text-sm">
                                <p class="flex gap-2"><x-icon name="x-mark" class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" /> <span class="bg4-mut"><strong class="text-brand-900">Before:</strong> {{ $s[2] }}</span></p>
                                <p class="flex gap-2"><x-icon name="check-circle" class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" /> <span class="bg4-mut"><strong class="text-brand-900">After:</strong> {{ $s[3] }}</span></p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-5">
                                <div class="rounded-xl p-3 text-center" style="background:var(--mist);"><p class="bg4-hd font-extrabold text-lg text-brand-800">{{ $s[4][0] }}</p><p class="text-[0.65rem] bg4-mut">{{ $s[4][1] }}</p></div>
                                <div class="rounded-xl p-3 text-center" style="background:var(--mist);"><p class="bg4-hd font-extrabold text-lg text-brand-800">{{ $s[5][0] }}</p><p class="text-[0.65rem] bg4-mut">{{ $s[5][1] }}</p></div>
                            </div>
                            <a href="{{ $demoUrl }}" target="_blank" class="bg4-btn bg4-btn-ghost mt-5 w-full"><x-icon name="play" class="w-4 h-4" /> Watch Story</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== SECTION 10 — TRADITIONAL vs DIGITAL ===== --}}
    <section class="py-20 md:py-28">
        <div class="max-w-5xl mx-auto px-5 sm:px-8">
            <div class="text-center reveal"><span class="bg4-eyebrow">The difference</span><h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3">Traditional business vs your digital business</h2></div>
            <div class="reveal mt-12 rounded-[24px] border overflow-hidden bg-white" style="border-color:var(--line);box-shadow:0 40px 80px -50px rgba(6,40,26,.3);">
                <div class="grid grid-cols-[1.4fr_1fr_1fr] text-sm border-b" style="border-color:var(--line);">
                    <div class="p-4 md:p-5 bg4-hd font-semibold text-brand-900"></div>
                    <div class="p-4 md:p-5 bg4-hd font-semibold text-slate-500 text-center" style="background:#fafbfa;">Traditional</div>
                    <div class="p-4 md:p-5 bg4-hd font-semibold text-brand-900 text-center" style="background:#e6f2eb;">Our Platform</div>
                </div>
                @foreach ([
                    ['Website','No website','Premium Microsite'],['Reviews','No reviews','Customer Reviews'],['Google','No SEO','Google Visibility'],
                    ['Products','No online products','Product Showcase'],['Enquiries','Manual only','WhatsApp Integration'],
                    ['Bookings','Phone tag','Appointment Booking'],['Insights','Guesswork','Business Analytics'],['Income','Sales only','+ Referral Income'],
                ] as $row)
                    <div class="bg4-row grid grid-cols-[1.4fr_1fr_1fr] text-sm border-b" style="border-color:var(--line);">
                        <div class="p-4 md:p-5 font-medium text-brand-900">{{ $row[0] }}</div>
                        <div class="p-4 md:p-5 text-center bg4-mut"><x-icon name="x-mark" class="w-4 h-4 text-red-400 inline" /> {{ $row[1] }}</div>
                        <div class="p-4 md:p-5 text-center text-brand-900 font-medium"><x-icon name="check-circle" class="w-4 h-4 text-green-600 inline" /> {{ $row[2] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== SECTION 11 — BUSINESS CATEGORIES ===== --}}
    <section class="py-20 md:py-28" style="background:var(--mist);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="text-center max-w-2xl mx-auto reveal"><span class="bg4-eyebrow">Built for every business</span><h2 class="bg4-hd font-extrabold text-3xl md:text-5xl mt-3 leading-tight">Find your business. See your demo.</h2></div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mt-12">
                @foreach ($categories as $i => $cat)
                    <a href="{{ $demoUrl }}" target="_blank" class="bg4-cat reveal h-52" style="transition-delay: {{ ($i % 4) * 0.06 }}s">
                        <div class="bg4-img absolute inset-0"><img src="{{ $u($cat[1], 600) }}" alt="{{ $cat[0] }}" loading="lazy" onerror="this.style.display='none'"></div>
                        <div class="absolute inset-0 z-10 flex flex-col justify-end p-5">
                            <h3 class="bg4-hd text-white font-bold">{{ $cat[0] }}</h3>
                            <p class="text-white/75 text-xs mt-0.5">{{ $cat[2] }}</p>
                            <span class="text-white/90 text-xs inline-flex items-center gap-1 mt-2">View Demo <x-icon name="arrow-right" class="w-3.5 h-3.5" /></span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== SECTION 12 — FINAL CTA ===== --}}
    <section class="relative overflow-hidden">
        <div class="bg4-img absolute inset-0"><img src="{{ $img['cta'] }}" alt="Entrepreneurs and customers" loading="lazy" onerror="this.style.display='none'"></div>
        <div class="absolute inset-0" style="background:linear-gradient(110deg,rgba(15,32,25,.94),rgba(31,72,52,.72) 55%,rgba(36,90,63,.45));"></div>
        <div class="bg4-mesh" style="opacity:.5;"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-5 sm:px-8 py-24 md:py-32 text-center text-white reveal">
            <h2 class="bg4-hd font-extrabold text-3xl md:text-5xl leading-[1.08]">Your business deserves more than just a social media page</h2>
            <p class="mt-6 text-lg text-white/85">Give customers a reason to trust you, discover you, and choose you. Build your professional digital presence, promote your products and services, and unlock new opportunities for growth.</p>
            <div class="mt-9 flex flex-wrap justify-center gap-3">
                <a href="{{ route('vip-plans.index') }}" class="bg4-btn bg4-btn-gold">Create My Business Page</a>
                <a href="{{ route('vip-plans.index') }}" class="bg4-btn bg4-btn-light">Become a VIP Member</a>
                <a href="{{ route('contact') }}" class="bg4-btn bg4-btn-light">Talk to Our Business Expert</a>
            </div>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="bg-white border-t" style="border-color:var(--line);">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 py-14 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="bg4-hd text-lg font-extrabold text-brand-800">{{ $siteName }}</a>
            <nav class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm bg4-mut">
                <a href="{{ route('products.index') }}" class="hover:text-brand-700">Products</a>
                <a href="{{ route('vip-plans.index') }}" class="hover:text-brand-700">VIP Plans</a>
                <a href="{{ route('blog.index') }}" class="hover:text-brand-700">Blog</a>
                <a href="{{ route('contact') }}" class="hover:text-brand-700">Contact</a>
                <a href="{{ $demoUrl }}" target="_blank" class="hover:text-brand-700">Live Demo</a>
            </nav>
            <p class="text-xs bg4-mut">© {{ date('Y') }} {{ $siteName }}</p>
        </div>
    </footer>

    <script>
        (function () {
            var header = document.getElementById('bg4-header');
            var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 40); };
            onScroll(); window.addEventListener('scroll', onScroll, { passive: true });

            // Microsite demo tabs
            var mocks = @json($mockData);
            var tabs = document.querySelectorAll('#bg4-tabs [data-tab]');
            tabs.forEach(function (t) {
                t.addEventListener('click', function () {
                    tabs.forEach(function (x) { x.classList.remove('is-active'); });
                    t.classList.add('is-active');
                    var m = mocks[+t.getAttribute('data-tab')];
                    var set = function (id, prop, val) { var el = document.getElementById(id); if (el) el[prop] = val; };
                    set('bg4-mock-img', 'src', m.img); set('bg4-mock-phone', 'src', m.img);
                    set('bg4-mock-name', 'textContent', m.name); set('bg4-mock-cat', 'textContent', m.cat);
                    set('bg4-mock-slug', 'textContent', m.slug); set('bg4-mock-ini', 'textContent', m.ini);
                });
            });

            // SEO typing effect
            var typeEl = document.getElementById('bg4-type');
            if (typeEl) {
                var qs = ['best gym near me', 'family doctor in my city', 'salon with online booking', 'protein supplements online'], qi = 0, ci = 0, del = false;
                (function tick() {
                    var q = qs[qi];
                    typeEl.textContent = q.substring(0, ci);
                    if (!del && ci < q.length) { ci++; setTimeout(tick, 70); }
                    else if (!del) { del = true; setTimeout(tick, 1400); }
                    else if (ci > 0) { ci--; setTimeout(tick, 35); }
                    else { del = false; qi = (qi + 1) % qs.length; setTimeout(tick, 300); }
                })();
            }

            // Chart grow when dashboard enters view
            var dash = document.getElementById('bg4-dash');
            if (dash && 'IntersectionObserver' in window) {
                new IntersectionObserver(function (en, o) { en.forEach(function (e) { if (e.isIntersecting) { dash.classList.add('is-charted'); o.disconnect(); } }); }, { threshold: 0.3 }).observe(dash);
            } else if (dash) { dash.classList.add('is-charted'); }

            // Success carousel
            var car = document.getElementById('bg4-scarousel');
            if (car && car.children.length) {
                var step = function () { return car.children[0].getBoundingClientRect().width + 24; };
                document.querySelectorAll('[data-scar-next]').forEach(function (b) { b.addEventListener('click', function () { car.scrollBy({ left: step(), behavior: 'smooth' }); }); });
                document.querySelectorAll('[data-scar-prev]').forEach(function (b) { b.addEventListener('click', function () { car.scrollBy({ left: -step(), behavior: 'smooth' }); }); });
            }
        })();
    </script>
</body>
</html>
