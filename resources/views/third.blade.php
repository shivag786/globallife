@php
    use Illuminate\Support\Str;

    $settings = app(\App\Services\SettingsService::class)->all();
    $siteName = $settings['site_title'] ?? 'Global Life';
    $money = fn ($n) => '₹'.number_format((float) $n, 0);

    // Royalty-free lifestyle photography (Unsplash). Each <img> sits over a brand
    // gradient and hides itself on error, so a missing photo never breaks the layout.
    $u = fn ($id, $w = 1200) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=70";

    $img = [
        'hero'        => $u('1600880292203-757bb62b4baf', 1400),
        'heroFlat'    => $u('1490645935967-10de6ba17061', 700),
        'cat_wellness'=> $u('1540555700478-4be289fbecef', 800),
        'cat_protein' => $u('1517836357463-d25dfeac3438', 800),
        'cat_health'  => $u('1505576399279-565b52d4ac71', 800),
        'cat_perfume' => $u('1592945403244-b3fbafd7f539', 800),
        'cat_personal'=> $u('1596462502278-27bfdc403348', 800),
        'cat_life'    => $u('1490818387583-1baba5e638af', 800),
        'why'         => $u('1512438248247-f0f2a5a8b7f0', 1100),
        'business'    => $u('1519389950473-47ba0277781c', 1200),
        'career'      => $u('1522071820081-009f0129c71c', 1300),
        'biz_doctor'  => $u('1612349317150-e413f6a5b16d', 700),
        'biz_restaurant'=> $u('1517248135467-4c7edcad34c4', 700),
        'biz_gym'     => $u('1534438327276-14e5300c3a48', 700),
        'biz_salon'   => $u('1560066984-138dadb4c035', 700),
        'biz_retail'  => $u('1441986300917-64674bd600d8', 700),
        'biz_maker'   => $u('1581091226825-a6a2a5aee158', 700),
    ];
    $avatars = [
        $u('1494790108377-be9c29b29330', 200), $u('1500648767791-00dcc994a43e', 200),
        $u('1438761681033-6461ffad8d80', 200), $u('1507003211169-0a1dd7228f2d', 200),
    ];
    $demoUrl = $heroMicrosite ? url($heroMicrosite->publicPath()) : route('vip-plans.index');

    // Section 6 — Real Customer Stories
    $featuredStory = [
        'name' => 'Neha Kapoor', 'location' => 'Lucknow, UP',
        'image' => $u('1511895426328-dc8714191300', 1000),
        'text' => 'We moved our whole family to Global Life wellness a year ago — better energy, cleaner ingredients, and honestly the taste my kids actually enjoy. Reordering is effortless.',
    ];
    $testimonials = [
        ['Priya Sharma', 'Yoga Instructor', 'Jhansi', 'Changed my mornings', 'The protein blend mixes clean and keeps me full through back-to-back classes. Genuine quality.', 'Family Prowell Nutrition', '12 Jun 2026', 0],
        ['Rahul Verma', 'Business Owner', 'Delhi', 'From customer to partner', 'Started as a buyer, now I run my own store on their platform. The microsite made me look properly professional.', 'Vitality Herbal Formula', '03 May 2026', 1],
        ['Anjali Mehta', 'Homemaker', 'Mumbai', 'Support that replies', 'The wellness range lifted my daily energy and their team actually answers — rare these days.', 'Jasmine Bloom EDP', '27 Apr 2026', 2],
        ['Dr. Sameer Rao', 'Physician', 'Pune', 'Ingredients I trust', 'I recommend these to patients because the sourcing is transparent and every batch is lab-tested.', 'Lavender Calm EDP', '19 Mar 2026', 3],
    ];
    $videoSrc = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4';

    // Section 7 — Choose Your Journey
    $journeyImg = [
        'shop' => $u('1607083206968-13611e3d76db', 800),
        'vip'  => $u('1573497019940-1c28c88b4f3e', 800),
        'grow' => $u('1556742049-0cfed4f6a45d', 800),
        'team' => $u('1497215728101-856f4ea42174', 800),
        'cta'  => $u('1521737604893-d14cc237f11d', 1500),
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => url('/third'),
        'description' => 'Premium wellness products and a digital business growth platform.',
    ];
    $faqs = [
        ['Are the products certified and lab-tested?', 'Yes. Every product is FSSAI-certified and independently lab-tested for purity and safety before it ships.'],
        ['Can I earn by recommending products?', 'Absolutely. Become a VIP member, enable the products you love, share your link, and earn commission on every sale — credited after delivery.'],
        ['I run a business. What do I get?', 'A premium digital microsite with your products, gallery, reviews, SEO, lead capture and appointment tools — a complete online presence in one link.'],
        ['How fast is delivery?', 'We deliver pan-India with tracked shipping, and orders over ₹999 ship free.'],
    ];
    $faqSchema = [
        '@context' => 'https://schema.org', '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question', 'name' => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], $faqs),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="w3">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} — Premium Wellness &amp; Your Digital Business, in One Ecosystem</title>
    <meta name="description" content="Premium, lab-tested wellness products delivered across India — plus a digital business platform: your own microsite, products, and recurring referral income. One powerful ecosystem.">
    <link rel="canonical" href="{{ url('/third') }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $siteName }} — Premium Wellness &amp; Digital Business">
    <meta property="og:description" content="Shop trusted wellness. Build your digital business. Earn recurring income.">
    <meta property="og:url" content="{{ url('/third') }}">
    <meta property="og:image" content="{{ $img['hero'] }}">
    <meta name="twitter:card" content="summary_large_image">

    @if (! empty($settings['favicon']))
        <link rel="icon" href="{{ asset('storage/'.$settings['favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES) !!}</script>

    <style>
        /* ============================================================
         * "Third" — premium wellness + digital-business landing.
         * Scoped w3-* system; light, editorial, image-forward.
         * ============================================================ */
        .w3 {
            --w3-ink: #14231b;
            --w3-muted: #5c6b63;
            --w3-line: #e7ede9;
            --w3-cream: #f7f5ef;
            --w3-mist: #eef4f0;
            --w3-brand: #245a3f;
            --w3-brand-600: #2c704c;
            --w3-brand-700: #1f4834;
            --w3-brand-900: #14231b;
            --w3-gold: #d4af37;
            --w3-head: 'Poppins', 'Manrope', ui-sans-serif, system-ui, sans-serif;
            scroll-behavior: smooth;
        }
        .w3 body, body.w3-body { background: #fff; color: var(--w3-ink); font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        .w3-head { font-family: var(--w3-head); letter-spacing: -0.02em; }
        .w3-eyebrow { font-family: var(--w3-head); text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.72rem; font-weight: 700; color: var(--w3-brand-600); }
        .w3-muted { color: var(--w3-muted); }

        /* Image containers with a graceful gradient fallback */
        .w3-img { position: relative; overflow: hidden; background: linear-gradient(135deg, #dfe9e3, #cfe0d5); }
        .w3-img > img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* Header */
        .w3-header { position: sticky; top: 0; z-index: 50; transition: background .3s ease, box-shadow .3s ease, border-color .3s ease; border-bottom: 1px solid transparent; }
        .w3-header.is-scrolled { background: rgba(255,255,255,0.82); backdrop-filter: blur(14px) saturate(160%); -webkit-backdrop-filter: blur(14px) saturate(160%); border-bottom-color: var(--w3-line); box-shadow: 0 6px 24px -18px rgba(0,0,0,.3); }
        .w3-navlink { font-size: 0.92rem; font-weight: 500; color: var(--w3-ink); transition: color .2s; }
        .w3-navlink:hover { color: var(--w3-brand-600); }

        /* Buttons — lift + shadow (magnetic-ish) */
        .w3-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 9999px; font-family: var(--w3-head); font-weight: 600; font-size: 0.95rem; padding: 0.85rem 1.6rem; transition: transform .2s ease, box-shadow .3s ease, background .3s; cursor: pointer; white-space: nowrap; }
        .w3-btn:hover { transform: translateY(-2px); }
        .w3-btn-primary { background: linear-gradient(135deg, var(--w3-brand-600), var(--w3-brand-700)); color: #fff; box-shadow: 0 14px 34px -12px rgba(36,90,63,.6); }
        .w3-btn-primary:hover { box-shadow: 0 20px 44px -12px rgba(36,90,63,.7); }
        .w3-btn-gold { background: linear-gradient(135deg, #e8c873, var(--w3-gold)); color: #1c1606; box-shadow: 0 14px 34px -12px rgba(212,175,55,.5); }
        .w3-btn-light { background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.6); backdrop-filter: blur(6px); }
        .w3-btn-light:hover { background: rgba(255,255,255,0.24); }
        .w3-btn-ghost { border: 1px solid var(--w3-line); color: var(--w3-brand-700); background: #fff; }
        .w3-btn-ghost:hover { border-color: var(--w3-brand); background: var(--w3-mist); }

        /* Hero */
        .w3-hero { position: relative; min-height: 750px; display: flex; align-items: center; overflow: hidden; }
        @media (max-width: 900px) { .w3-hero { min-height: auto; } }
        .w3-hero-bg { position: absolute; inset: 0; z-index: 0; }
        .w3-hero-bg::after { content: ''; position: absolute; inset: 0; background: linear-gradient(100deg, rgba(15,32,25,.86) 0%, rgba(15,32,25,.62) 42%, rgba(15,32,25,.15) 100%); }
        @media (max-width: 900px) { .w3-hero-bg::after { background: linear-gradient(180deg, rgba(15,32,25,.72), rgba(15,32,25,.9)); } }

        /* Cards & hover */
        .w3-cat { position: relative; border-radius: 20px; overflow: hidden; display: block; }
        .w3-cat img { transition: transform .7s cubic-bezier(.2,.7,.2,1); }
        .w3-cat:hover img { transform: scale(1.08); }
        .w3-cat::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,32,25,.85) 8%, rgba(15,32,25,.15) 60%, transparent); transition: opacity .3s; }
        .w3-card { background: #fff; border: 1px solid var(--w3-line); border-radius: 20px; transition: transform .35s ease, box-shadow .35s ease; }
        .w3-card:hover { transform: translateY(-6px); box-shadow: 0 30px 55px -30px rgba(6,40,26,.32); }
        .w3-prod-img { background: linear-gradient(180deg, #fff, #eef4f0); }
        .w3-journey { transition: transform .4s ease, box-shadow .4s ease, border-color .4s ease; }
        .w3-journey:hover { transform: translateY(-8px); border-color: var(--w3-brand) !important; box-shadow: 0 34px 60px -28px rgba(6,40,26,.42), 0 0 0 1px rgba(36,90,63,.18); }
        #w3-slider { scrollbar-width: none; }
        #w3-slider::-webkit-scrollbar { display: none; }
        .w3-dot { width: 8px; height: 8px; border-radius: 9999px; background: rgba(20,35,27,.2); transition: width .3s, background .3s; }
        .w3-dot.is-active { width: 22px; background: var(--w3-brand); }

        /* Floating hero cards */
        @media (prefers-reduced-motion: no-preference) {
            .w3-float { animation: w3-float 5s ease-in-out infinite; }
            .w3-float.d1 { animation-delay: .8s; }
            @keyframes w3-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
            .w3-marquee-track { animation: w3-marquee 26s linear infinite; }
            @keyframes w3-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        }

        /* Marquee */
        .w3-marquee { overflow: hidden; }
        .w3-marquee-track { display: flex; width: max-content; gap: 3.5rem; }

        /* Timeline */
        .w3-step { position: relative; }
        .w3-step::before { content: ''; position: absolute; left: 27px; top: 60px; bottom: -28px; width: 2px; background: linear-gradient(var(--w3-brand), transparent); }
        .w3-step:last-child::before { display: none; }

        .w3-underline { position: relative; }
        .w3-underline::after { content:''; position:absolute; left:0; right:0; bottom:.06em; height:.32em; background: rgba(212,175,55,.35); z-index:-1; border-radius:2px; }
    </style>
</head>
<body class="w3-body">

    {{-- ============================ HEADER ============================ --}}
    <header class="w3-header" id="w3-header">
        <div class="mw-7xl mx-auto px-5 px-sm-8 h-18 d-flex align-items-center justify-content-between" style="height:4.5rem;">
            <a href="{{ url('/') }}" class="w3-head fs-xl fw-bolder text-brand-emphasis d-flex align-items-center gap-2">
                @if (! empty($settings['site_logo']))
                    <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="{{ $siteName }}" class="h-9 w-auto">
                @else
                    <span class="w-2.5 h-2.5 rounded-pill" style="background:var(--w3-brand);box-shadow:0 0 10px var(--w3-brand);"></span> {{ $siteName }}
                @endif
            </a>
            <nav class="d-none d-lg-flex align-items-center gap-8">
                <a href="#shop" class="w3-navlink">Shop</a>
                <a href="#why" class="w3-navlink">Why Us</a>
                <a href="#business" class="w3-navlink">For Business</a>
                <a href="#earn" class="w3-navlink">Earn</a>
                <a href="#reviews" class="w3-navlink">Reviews</a>
                <a href="{{ route('blog.index') }}" class="w3-navlink">Blog</a>
            </nav>
            <div class="d-flex align-items-center gap-3 gap-sm-5">
                @include('partials.cart-icon')
                <a href="{{ route('login') }}" class="w3-navlink d-none d-sm-inline">Log in</a>
                <a href="{{ route('vip-plans.index') }}" class="w3-btn w3-btn-primary" style="padding:.6rem 1.15rem;font-size:.85rem;">Become VIP</a>
            </div>
        </div>
    </header>

    {{-- ============================ HERO ============================ --}}
    <section class="w3-hero">
        <div class="w3-hero-bg w3-img">
            <img src="{{ $img['hero'] }}" alt="Wellness entrepreneur" onerror="this.style.display='none'" fetchpriority="high">
        </div>
        <div class="position-relative z-1 mw-7xl mx-auto px-5 px-sm-8 py-24 py-md-0 w-100">
            <div class="d-grid grid-template-lg gap-12 align-items-center" style="--cols-lg: minmax(0, 1.05fr) minmax(0, .95fr)">
                <div class="text-white">
                    <span class="w3-eyebrow animate-fade-in-up" style="color:#e8c873;">Premium Wellness · Digital Business</span>
                    <h1 class="w3-head fw-bolder lh-1 mt-5 fs-4xl fs-5xl">
                        Feel your best.<br>Then <span class="w3-underline">build a business</span> around it.
                    </h1>
                    <p class="mt-6 fs-lg text-white text-opacity-75 mw-xl">
                        Lab-tested wellness products people genuinely reorder — and a complete digital
                        platform to sell them, with your own microsite and recurring income.
                    </p>
                    <div class="mt-8 d-flex flex-wrap gap-3">
                        <a href="#shop" class="w3-btn w3-btn-gold">Explore Products</a>
                        <a href="{{ route('vip-plans.index') }}" class="w3-btn w3-btn-light">Become a VIP Member</a>
                        <a href="{{ $demoUrl }}" class="w3-btn w3-btn-light d-inline-flex" target="_blank">
                            <x-icon name="play" class="w-4 h-4" /> Watch a live store
                        </a>
                    </div>
                    <div class="mt-9 d-flex flex-wrap align-items-center column-gap-6 row-gap-2 fs-sm text-white text-opacity-75">
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="shield-check" class="w-4 h-4" style="color:#e8c873;" /> FSSAI Certified</span>
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="beaker" class="w-4 h-4" style="color:#e8c873;" /> Lab Tested</span>
                        <span class="d-inline-flex align-items-center gap-2"><x-icon name="truck" class="w-4 h-4" style="color:#e8c873;" /> Pan-India Delivery</span>
                    </div>
                </div>

                {{-- Floating imagery --}}
                <div class="d-none d-lg-block position-relative" id="w3-hero-media">
                    <div class="w3-img w3-float position-absolute top-0 end-4 w-72 h-96 rounded-4 shadow-lg border-4 border-white border-opacity-25" data-depth="18">
                        <img src="{{ $img['hero'] }}" alt="Entrepreneur with wellness products" onerror="this.style.display='none'">
                    </div>
                    <div class="w3-img w3-float d1 position-absolute bottom-2 start-0 w-52 h-52 rounded-4 shadow-lg border-4 border-white border-opacity-25" data-depth="34">
                        <img src="{{ $img['heroFlat'] }}" alt="Healthy lifestyle" onerror="this.style.display='none'">
                    </div>
                    @if ($products->isNotEmpty())
                        <div class="w3-float d1 position-absolute bottom-n4 end-16 bg-body rounded-4 shadow-lg p-3 d-flex align-items-center gap-3 w-56" data-depth="46">
                            <div class="w-12 h-12 rounded-3 w3-prod-img d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0">
                                @if ($products->first()->main_image)
                                    <img src="{{ asset('storage/'.$products->first()->main_image) }}" alt="" class="w-100 h-100 object-fit-contain p-1">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="fs-xs fw-semibold text-brand-emphasis text-truncate">{{ $products->first()->name }}</p>
                                <p class="fs-xs text-gold fw-bold">{{ $products->first()->hasPrice() ? $money($products->first()->sellingPrice()) : '' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ============================ TRUST BAR ============================ --}}
    <section class="border-bottom" style="border-color:var(--w3-line);">
        <div class="mw-7xl mx-auto px-5 px-sm-8 py-10 d-grid grid-cols-2 grid-cols-md-4 gap-8 text-center">
            @foreach ([['2.5L+','Happy customers'],['19+','Cities served'],['750+','Business partners'],['1.2Cr+','Products sold']] as $stat)
                <div class="reveal">
                    <p class="w3-head fw-bolder fs-3xl fs-4xl text-brand-emphasis" data-countup="{{ $stat[0] }}">{{ $stat[0] }}</p>
                    <p class="w3-muted fs-sm mt-1">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================ SHOP BY CATEGORY ============================ --}}
    <section id="shop" class="py-20 py-md-28" style="background:var(--w3-cream);">
        <div class="mw-7xl mx-auto px-5 px-sm-8">
            <div class="mw-2xl reveal">
                <span class="w3-eyebrow">Shop by category</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">Everything for a healthier you</h2>
            </div>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-5 mt-12">
                @foreach ([
                    ['Wellness','cat_wellness'],['Protein','cat_protein'],['Health Care','cat_health'],
                    ['Perfume','cat_perfume'],['Personal Care','cat_personal'],['Lifestyle','cat_life'],
                ] as $i => $cat)
                    <a href="{{ route('products.index') }}" class="w3-cat reveal h-64" style="transition-delay: {{ ($i % 3) * 0.08 }}s">
                        <div class="w3-img position-absolute top-0 start-0 w-100 h-100"><img src="{{ $img[$cat[1]] }}" alt="{{ $cat[0] }}" loading="lazy" onerror="this.style.display='none'"></div>
                        <div class="position-absolute top-0 start-0 w-100 h-100 z-1 d-flex align-items-end p-6">
                            <div>
                                <h3 class="w3-head text-white fw-bold fs-xl">{{ $cat[0] }}</h3>
                                <span class="text-white text-opacity-75 fs-sm d-inline-flex align-items-center gap-1 mt-1">Shop now <x-icon name="arrow-right" class="w-4 h-4" /></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ FEATURED PRODUCTS ============================ --}}
    @if ($products->isNotEmpty())
        <section class="py-20 py-md-28">
            <div class="mw-7xl mx-auto px-5 px-sm-8">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-4 reveal">
                    <div class="mw-xl">
                        <span class="w3-eyebrow">Bestsellers</span>
                        <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">Loved &amp; reordered</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="w3-btn w3-btn-ghost">View all products</a>
                </div>
                <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6 mt-12">
                    @foreach ($products->take(4) as $i => $product)
                        @php $rating = (int) round((float) $product->rating); @endphp
                        <a href="{{ route('products.show', $product) }}" class="w3-card reveal d-flex flex-column overflow-hidden" style="transition-delay: {{ ($i % 4) * 0.07 }}s">
                            <div class="position-relative aspect-square w3-prod-img d-flex align-items-center justify-content-center overflow-hidden">
                                @if ($product->main_image)
                                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-contain p-6" loading="lazy">
                                @endif
                                @if ($product->discountPercentage())
                                    <span class="position-absolute top-3 start-3 fs-xs fw-bold text-white px-2 py-1 rounded-pill bg-success">{{ $product->discountPercentage() }}% OFF</span>
                                @endif
                            </div>
                            <div class="p-5 d-flex flex-column flex-grow-1">
                                @if ($product->review_count > 0)
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <div class="d-flex">@for ($s=1;$s<=5;$s++)<x-icon name="star" class="w-3.5 h-3.5 {{ $s <= $rating ? 'text-gold' : 'text-body-tertiary' }}" :filled="$s <= $rating" />@endfor</div>
                                        <span class="fs-xs w3-muted">({{ $product->review_count }})</span>
                                    </div>
                                @endif
                                <h3 class="w3-head fw-semibold text-brand-emphasis lh-sm line-clamp-2">{{ $product->name }}</h3>
                                <div class="mt-auto pt-3 d-flex align-items-baseline gap-2">
                                    @if ($product->hasPrice())
                                        <span class="fs-lg fw-bold text-brand-emphasis">{{ $money($product->sellingPrice()) }}</span>
                                        @if ($product->hasDiscount())<span class="fs-sm w3-muted text-decoration-line-through">{{ $money($product->mrp) }}</span>@endif
                                    @else
                                        <span class="fs-sm fw-medium text-brand">Enquire for price</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ WHY OUR PRODUCTS ============================ --}}
    <section id="why" class="py-20 py-md-28" style="background:var(--w3-mist);">
        <div class="mw-7xl mx-auto px-5 px-sm-8 d-grid grid-cols-lg-2 gap-14 align-items-center">
            <div class="w3-img reveal rounded-4 aspect-square shadow-lg" data-reveal="left">
                <img src="{{ $img['why'] }}" alt="Natural wellness ingredients" loading="lazy" onerror="this.style.display='none'">
            </div>
            <div class="reveal" data-reveal="right">
                <span class="w3-eyebrow">Why our products</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">Quality you can actually verify</h2>
                <div class="d-grid grid-cols-sm-2 column-gap-8 row-gap-6 mt-8">
                    @foreach ([
                        ['leaf','Natural Ingredients','Named sources, no hidden fillers.'],
                        ['sparkles','Premium Quality','GMP-compliant, small-batch care.'],
                        ['truck','Fast Delivery','Tracked, pan-India, free over ₹999.'],
                        ['shield-check','Trusted Brand','FSSAI-certified &amp; lab-tested.'],
                        ['check-circle','Money-Back','7-day easy returns, no questions.'],
                        ['chat-bubble','Customer Support','Real humans, every day.'],
                    ] as $b)
                        <div class="d-flex align-items-start gap-3">
                            <span class="w-10 h-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="background:#e6f2eb;"><x-icon name="{{ $b[0] }}" class="w-5 h-5 text-brand" /></span>
                            <div>
                                <p class="w3-head fw-semibold text-brand-emphasis">{{ $b[1] }}</p>
                                <p class="w3-muted fs-sm">{!! $b[2] !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================ SECTION 6 — REAL CUSTOMER STORIES ============================ --}}
    <section id="reviews" class="position-relative py-20 py-md-28 overflow-hidden" style="background:linear-gradient(180deg,#fff,var(--w3-mist));">
        <div class="position-absolute top-n20 start-n24 w-96 h-96 rounded-pill" style="background:rgba(36,90,63,.10);"></div>
        <div class="position-absolute bottom-0 end-0 rounded-pill" style="background:rgba(212,175,55,.12);"></div>

        <div class="position-relative mw-7xl mx-auto px-5 px-sm-8">
            <div class="text-center mw-2xl mx-auto reveal">
                <span class="w3-eyebrow">Real customer stories</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">Real people. Real results.</h2>
            </div>

            <div class="d-grid grid-template-lg gap-8 mt-12 align-items-stretch" style="--cols-lg: minmax(0, 1.05fr) minmax(0, 1fr)">
                {{-- LEFT: featured story + video --}}
                <div class="reveal" data-reveal="left">
                    <div class="position-relative rounded-4 overflow-hidden h-100 shadow-lg w3-img">
                        <img src="{{ $featuredStory['image'] }}" alt="{{ $featuredStory['name'] }}" class="position-absolute top-0 start-0 w-100 h-100" loading="lazy" onerror="this.style.display='none'">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(to top,rgba(15,32,25,.92) 8%,rgba(15,32,25,.35) 55%,transparent);"></div>
                        <button type="button" data-modal-open="#w3-video-modal" data-video-open aria-label="Watch story"
                                class="btn btn-link p-0 text-decoration-none position-absolute top-6 end-6 w-16 h-16 rounded-pill bg-white bg-opacity-100 d-flex align-items-center justify-content-center shadow-lg animate-pulse-ring">
                            <x-icon name="play" class="w-7 h-7 text-brand" />
                        </button>
                        <div class="position-absolute start-0 end-0 bottom-0 p-7 text-white">
                            <div class="d-flex text-gold mb-2">@for ($s=0;$s<5;$s++)<x-icon name="star" class="w-4 h-4" :filled="true" />@endfor</div>
                            <p class="fs-lg lh-lg mw-md">“{{ $featuredStory['text'] }}”</p>
                            <div class="mt-5">
                                <p class="w3-head fw-bold">{{ $featuredStory['name'] }}</p>
                                <p class="text-white text-opacity-75 fs-sm d-inline-flex align-items-center gap-1"><x-icon name="check-circle" class="w-3.5 h-3.5 text-success" /> Verified purchase · {{ $featuredStory['location'] }}</p>
                            </div>
                            <div class="mt-5">
                                <button type="button" data-modal-open="#w3-video-modal" data-video-open class="btn btn-link p-0 text-decoration-none w3-btn w3-btn-gold" style="padding:.7rem 1.3rem;"><x-icon name="play" class="w-4 h-4" /> Watch Story</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: testimonial slider --}}
                <div class="reveal" data-reveal="right">
                    <div class="position-relative">
                        <div id="w3-slider" class="d-flex overflow-x-auto gap-4 pb-2" style="scrollbar-width:none;-ms-overflow-style:none;">
                            @foreach ($testimonials as $t)
                                <article class="flex-shrink-0 w-100 rounded-4 p-6 border"
                                         style="background:rgba(255,255,255,.65);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-color:rgba(255,255,255,.6);box-shadow:0 24px 50px -30px rgba(6,40,26,.35);">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="w3-img w-14 h-14 rounded-pill overflow-hidden"><img src="{{ $avatars[$t[7]] }}" alt="{{ $t[0] }}" loading="lazy" onerror="this.style.display='none'"></div>
                                        <div class="min-w-0">
                                            <p class="w3-head fw-bold text-brand-emphasis">{{ $t[0] }}</p>
                                            <p class="w3-muted fs-xs">{{ $t[1] }} · {{ $t[2] }}</p>
                                        </div>
                                        <span class="ms-auto d-inline-flex align-items-center gap-1 fw-semibold text-success bg-success-subtle px-2 py-1 rounded-pill"><x-icon name="check-circle" class="w-3 h-3" /> Verified</span>
                                    </div>
                                    <div class="d-flex text-gold mt-4">@for ($s=0;$s<5;$s++)<x-icon name="star" class="w-4 h-4" :filled="true" />@endfor</div>
                                    <h3 class="w3-head fw-bold text-brand-emphasis mt-3">{{ $t[3] }}</h3>
                                    <p class="mt-1 lh-lg">“{{ $t[4] }}”</p>
                                    <div class="d-flex align-items-center justify-content-between mt-5 pt-4 border-top fs-xs w3-muted" style="border-color:rgba(20,35,27,.08);">
                                        <span class="d-inline-flex align-items-center gap-1"><x-icon name="shopping-bag" class="w-3.5 h-3.5" /> {{ $t[5] }}</span>
                                        <span>{{ $t[6] }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-5">
                            <div class="d-flex gap-2" id="w3-slider-dots"></div>
                            <div class="d-flex gap-2">
                                <button type="button" data-slider-prev class="btn btn-link p-0 text-decoration-none w-10 h-10 rounded-pill border d-flex align-items-center justify-content-center text-brand" style="border-color:var(--w3-line);" aria-label="Previous"><x-icon name="chevron-left" class="w-5 h-5" /></button>
                                <button type="button" data-slider-next class="btn btn-link p-0 text-decoration-none w-10 h-10 rounded-pill border d-flex align-items-center justify-content-center text-brand" style="border-color:var(--w3-line);" aria-label="Next"><x-icon name="chevron-right" class="w-5 h-5" /></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STATS STRIP --}}
            <div class="d-grid grid-cols-2 grid-cols-md-4 gap-5 mt-16">
                @foreach ([['10,000+','Happy Customers'],['25,000+','Products Delivered'],['4.9★','Average Rating'],['98%','Customer Satisfaction']] as $st)
                    <div class="reveal text-center rounded-4 py-7 px-4" style="background:rgba(255,255,255,.7);backdrop-filter:blur(8px);border:1px solid var(--w3-line);">
                        <p class="w3-head fw-bolder fs-3xl fs-4xl text-brand-emphasis" data-countup="{{ $st[0] }}">{{ $st[0] }}</p>
                        <p class="w3-muted fs-sm mt-1">{{ $st[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ BUILD YOUR DIGITAL BUSINESS ============================ --}}
    <section id="business" class="py-20 py-md-28" style="background:var(--w3-brand-900);">
        <div class="mw-7xl mx-auto px-5 px-sm-8 d-grid grid-cols-lg-2 gap-14 align-items-center">
            <div class="text-white reveal" data-reveal="left">
                <span class="w3-eyebrow" style="color:#e8c873;">Build your digital business</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">Your own storefront, live in minutes</h2>
                <p class="mt-5 text-white text-opacity-75 fs-lg mw-lg">Not another catalogue to memorise. Get a premium microsite, enable the products you love, and start earning — all from one link.</p>
                <div class="d-grid grid-cols-sm-2 gap-4 mt-8">
                    @foreach ([['sparkles','VIP Membership'],['photo','Digital Microsite'],['eye','SEO &amp; Visibility'],['inbox','Lead Generation'],['tag','Referral Income'],['users','Community &amp; Training']] as $f)
                        <div class="d-flex align-items-center gap-3">
                            <span class="w-9 h-9 rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="background:rgba(232,200,115,.14);"><x-icon name="{{ $f[0] }}" class="w-5 h-5" style="color:#e8c873;" /></span>
                            <span class="text-white text-opacity-100">{!! $f[1] !!}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-9 d-flex flex-wrap gap-3">
                    <a href="{{ route('vip-plans.index') }}" class="w3-btn w3-btn-gold">Start your business</a>
                    <a href="{{ $demoUrl }}" target="_blank" class="w3-btn w3-btn-light">See a live microsite</a>
                </div>
            </div>
            <div class="w3-img reveal rounded-4 aspect-video shadow-lg" data-reveal="right">
                <img src="{{ $img['business'] }}" alt="Business owner building strategy" loading="lazy" onerror="this.style.display='none'">
            </div>
        </div>
    </section>

    {{-- ============================ EARN WITH US (TIMELINE) ============================ --}}
    <section id="earn" class="py-20 py-md-28">
        <div class="mw-3xl mx-auto px-5 px-sm-8">
            <div class="text-center reveal">
                <span class="w3-eyebrow">Earn with us</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">How the income works</h2>
                <p class="w3-muted mt-4">Transparent, tracked, and paid after delivery — no black boxes.</p>
            </div>
            <div class="mt-14 d-flex flex-column gap-8">
                @foreach ([
                    ['Become a VIP','Join and unlock your storefront and dashboard.'],
                    ['Enable Products','Switch on the catalog products you want to sell.'],
                    ['Share Your Link','One premium link — send it anywhere.'],
                    ['Customer Purchases','They buy from your microsite, securely.'],
                    ['Company Delivers','We handle fulfilment and tracked shipping.'],
                    ['Commission Credited','Your share lands in your wallet after delivery.'],
                ] as $i => $step)
                    <div class="w3-step d-flex gap-5 reveal" style="transition-delay: {{ $i * 0.06 }}s">
                        <div class="flex-shrink-0 w-14 h-14 rounded-4 d-flex align-items-center justify-content-center w3-head fw-bolder text-white fs-lg" style="background:linear-gradient(135deg,var(--w3-brand-600),var(--w3-brand-700));">{{ $i + 1 }}</div>
                        <div class="pt-2">
                            <h3 class="w3-head fw-bold text-brand-emphasis fs-lg">{{ $step[0] }}</h3>
                            <p class="w3-muted mt-1">{{ $step[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ BUSINESS OWNER + CATEGORY GRID ============================ --}}
    <section class="py-20 py-md-28" style="background:var(--w3-cream);">
        <div class="mw-7xl mx-auto px-5 px-sm-8">
            <div class="text-center mw-2xl mx-auto reveal">
                <span class="w3-eyebrow">Built for every business</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">A professional presence for any business</h2>
                <p class="w3-muted mt-4">Microsite, gallery, reviews, products, SEO, lead capture, appointments &amp; contact — all in one.</p>
            </div>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-5 mt-12">
                @foreach ([
                    ['Doctors','biz_doctor'],['Restaurants','biz_restaurant'],['Gyms','biz_gym'],
                    ['Salons','biz_salon'],['Retail Shops','biz_retail'],['Manufacturers','biz_maker'],
                ] as $i => $b)
                    <a href="{{ $demoUrl }}" target="_blank" class="w3-cat reveal h-56" style="transition-delay: {{ ($i % 3) * 0.07 }}s">
                        <div class="w3-img position-absolute top-0 start-0 w-100 h-100"><img src="{{ $img[$b[1]] }}" alt="{{ $b[0] }}" loading="lazy" onerror="this.style.display='none'"></div>
                        <div class="position-absolute top-0 start-0 w-100 h-100 z-1 d-flex align-items-end p-6">
                            <div>
                                <h3 class="w3-head text-white fw-bold fs-lg">{{ $b[0] }}</h3>
                                <span class="text-white text-opacity-75 fs-sm d-inline-flex align-items-center gap-1 mt-1">View demo microsite <x-icon name="arrow-right" class="w-4 h-4" /></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-8">
                @foreach (['Consultant','Influencer','Freelancer','Student','Housewife','Coach','Photographer','Real Estate'] as $tag)
                    <span class="fs-sm px-4 py-2 rounded-pill bg-body border" style="border-color:var(--w3-line);color:var(--w3-muted);">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ BLOG ============================ --}}
    @if ($posts->isNotEmpty())
        <section class="py-20 py-md-28">
            <div class="mw-7xl mx-auto px-5 px-sm-8">
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-4 reveal">
                    <div><span class="w3-eyebrow">From the journal</span><h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3">Ideas &amp; insights</h2></div>
                    <a href="{{ route('blog.index') }}" class="w3-btn w3-btn-ghost">Read the blog</a>
                </div>
                <div class="d-grid grid-cols-md-3 gap-6 mt-12">
                    @foreach ($posts as $i => $post)
                        <a href="{{ route('blog.show', $post) }}" class="w3-card reveal overflow-hidden d-flex flex-column" style="transition-delay: {{ $i * 0.08 }}s">
                            <div class="w3-img aspect-video">
                                @if ($post->featured_image)
                                    <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" loading="lazy" onerror="this.style.display='none'">
                                @endif
                            </div>
                            <div class="p-5 d-flex flex-column flex-grow-1">
                                @if ($post->category)<span class="w3-eyebrow" style="font-size:0.62rem;">{{ $post->category }}</span>@endif
                                <h3 class="w3-head fw-semibold text-brand-emphasis mt-1 lh-sm line-clamp-2">{{ $post->title }}</h3>
                                <p class="w3-muted fs-sm mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                                <span class="text-brand fs-sm fw-medium mt-auto pt-3 d-inline-flex align-items-center gap-1">Read more <x-icon name="arrow-right" class="w-4 h-4" /></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ FAQ ============================ --}}
    <section class="py-20 py-md-28" style="background:var(--w3-mist);">
        <div class="mw-3xl mx-auto px-5 px-sm-8">
            <div class="text-center reveal"><span class="w3-eyebrow">FAQ</span><h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3">Good questions, clear answers</h2></div>
            <div class="mt-10 d-flex flex-column gap-3">
                @foreach ($faqs as $f)
                    <details class="bg-body rounded-4 border reveal" style="border-color:var(--w3-line);">
                        <summary class="d-flex align-items-center justify-content-between gap-4 list-unstyled p-5 w3-head fw-semibold text-brand-emphasis">
                            {{ $f[0] }}
                            <x-icon name="plus" class="w-5 h-5 text-brand flex-shrink-0 open-hide" />
                            <x-icon name="minus" class="w-5 h-5 text-brand flex-shrink-0 d-none open-show" />
                        </summary>
                        <p class="w3-muted px-5 pb-5 -n">{{ $f[1] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================ CAREER ============================ --}}
    <section class="position-relative py-24 py-md-32 overflow-hidden">
        <div class="w3-img position-absolute top-0 start-0 w-100 h-100"><img src="{{ $img['career'] }}" alt="Modern office team" loading="lazy" onerror="this.style.display='none'"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(100deg,rgba(15,32,25,.9),rgba(15,32,25,.55));"></div>
        <div class="position-relative z-1 mw-7xl mx-auto px-5 px-sm-8 text-white reveal">
            <span class="w3-eyebrow" style="color:#e8c873;">Careers</span>
            <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 mw-xl lh-sm">Join a team building India's wellness ecosystem</h2>
            <p class="mt-4 text-white text-opacity-75 mw-lg">We're hiring across product, technology, operations and partner success.</p>
            <a href="{{ route('contact') }}" class="w3-btn w3-btn-gold mt-8">View open positions</a>
        </div>
    </section>

    {{-- ============================ SECTION 7 — CHOOSE YOUR JOURNEY ============================ --}}
    <section class="position-relative py-20 py-md-28 overflow-hidden" style="background:var(--w3-cream);">
        <div class="position-absolute top-10 end-n24 w-96 h-96 rounded-pill" style="background:rgba(36,90,63,.08);"></div>
        <div class="position-absolute bottom-n24 start-n20 rounded-pill" style="background:rgba(212,175,55,.10);"></div>

        <div class="position-relative mw-7xl mx-auto px-5 px-sm-8">
            <div class="text-center mw-2xl mx-auto reveal">
                <span class="w3-eyebrow">Choose your journey</span>
                <h2 class="w3-head fw-bolder fs-3xl fs-5xl mt-3 lh-sm">Choose Your Journey</h2>
                <p class="w3-muted mt-4">Whether you want to improve your health, grow your business, earn additional income, or build your career — there's a path here designed for someone exactly like you.</p>
            </div>

            <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6 mt-12">
                @foreach ([
                    ['Shop Premium Products','shop','Discover premium wellness, health, nutrition and personal care products for a healthier lifestyle.',['Premium Quality','Genuine Products','Fast Delivery','Secure Payments'],'Explore Products', route('products.index')],
                    ['Become a VIP Partner','vip','Build your own digital business page, recommend official products, and earn commission on every successful sale.',['Personal Microsite','Official Product Store','Referral Income','Digital Business Tools'],'Become VIP', route('vip-plans.index')],
                    ['Grow Your Business','grow','Promote your business with an SEO-ready microsite, reviews, gallery, booking and lead-generation tools.',['Business Profile','Google Visibility','Reviews & WhatsApp','Lead Generation'],'Explore Solutions', $demoUrl],
                    ['Join Our Team','team','Join our growing team and help build the future of wellness, technology and digital business.',['Flexible Career','Professional Growth','Training','Modern Work Culture'],'View Careers', route('contact')],
                ] as $i => $c)
                    <div class="w3-journey reveal bg-body rounded-4 overflow-hidden border d-flex flex-column" style="border-color:var(--w3-line);transition-delay: {{ $i * 0.09 }}s;">
                        <div class="position-relative aspect-video overflow-hidden w3-img">
                            <img src="{{ $journeyImg[$c[1]] }}" alt="{{ $c[0] }}" class="position-absolute top-0 start-0 w-100 h-100" loading="lazy" onerror="this.style.display='none'">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(to top,rgba(15,32,25,.5),transparent 60%);"></div>
                        </div>
                        <div class="p-6 d-flex flex-column flex-grow-1">
                            <h3 class="w3-head fw-bold text-brand-emphasis fs-lg">{{ $c[0] }}</h3>
                            <p class="w3-muted fs-sm mt-2">{{ $c[2] }}</p>
                            <ul class="mt-4 d-flex flex-column gap-2">
                                @foreach ($c[3] as $feat)
                                    <li class="d-flex align-items-center gap-2 fs-sm"><x-icon name="check-circle" class="w-4 h-4 text-brand flex-shrink-0" /> {{ $feat }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ $c[5] }}" class="w3-btn w3-btn-primary mt-6 w-100">{{ $c[4] }} <x-icon name="arrow-right" class="w-4 h-4" /></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- BOTTOM CTA --}}
        <div class="position-relative mw-7xl mx-auto px-5 px-sm-8 mt-16">
            <div class="position-relative rounded-4 overflow-hidden shadow-lg">
                <div class="w3-img position-absolute top-0 start-0 w-100 h-100"><img src="{{ $journeyImg['cta'] }}" alt="One platform, multiple opportunities" loading="lazy" onerror="this.style.display='none'"></div>
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background:linear-gradient(110deg,rgba(15,32,25,.92),rgba(31,72,52,.7) 55%,rgba(36,90,63,.4));"></div>
                <div class="position-relative z-1 px-6 py-16 px-md-16 py-md-20 text-white text-center mw-3xl mx-auto reveal">
                    <h2 class="w3-head fw-bolder fs-3xl fs-5xl lh-sm">One Platform. Multiple Opportunities.</h2>
                    <p class="mt-5 text-white text-opacity-75 fs-lg">Whether you're a customer, entrepreneur, business owner or job seeker — grow with premium products, digital business solutions and real earning opportunities.</p>
                    <div class="mt-9 d-flex flex-wrap justify-content-center gap-3">
                        <a href="#shop" class="w3-btn w3-btn-gold">Explore Products</a>
                        <a href="{{ route('vip-plans.index') }}" class="w3-btn w3-btn-light">Become a VIP Partner</a>
                        <a href="{{ route('contact') }}" class="w3-btn w3-btn-light">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================ FOOTER ============================ --}}
    <footer class="bg-body border-top" style="border-color:var(--w3-line);">
        <div class="mw-7xl mx-auto px-5 px-sm-8 py-16">
            <div class="d-grid grid-template-md gap-10" style="--cols-md: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr)">
                <div>
                    <p class="w3-head fs-xl fw-bolder text-brand-emphasis">{{ $siteName }}</p>
                    <p class="w3-muted fs-sm mt-3 mw-xs">Premium wellness products and a digital business platform — one powerful ecosystem.</p>
                    <form method="POST" action="{{ route('enquiry.store') }}" class="mt-5 d-flex gap-2 mw-xs">
                        @csrf
                        <input type="hidden" name="source" value="homepage">
                        <input type="hidden" name="name" value="Newsletter subscriber">
                        <input type="email" name="email" required placeholder="Your email" class="flex-grow-1 rounded-pill fs-sm">
                        <button type="submit" class="btn btn-link p-0 text-decoration-none w3-btn w3-btn-primary" style="padding:.6rem 1.1rem;font-size:.85rem;">Join</button>
                    </form>
                </div>
                @foreach ([
                    'Products' => [['Shop All', route('products.index')], ['VIP Plans', route('vip-plans.index')]],
                    'Business' => [['Become VIP', route('vip-plans.index')], ['Live Microsite', $demoUrl]],
                    'Company' => [['Blog', route('blog.index')], ['Events', route('events.index')], ['Contact', route('contact')]],
                    'Support' => [['Log in', route('login')], ['Contact', route('contact')]],
                ] as $col => $links)
                    <div>
                        <p class="w3-eyebrow" style="color:var(--w3-muted);">{{ $col }}</p>
                        <ul class="mt-4 d-flex flex-column gap-3 fs-sm">
                            @foreach ($links as $l)<li><a href="{{ $l[1] }}" class="w3-muted">{{ $l[0] }}</a></li>@endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <div class="mt-12 pt-6 border-top d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 fs-xs w3-muted" style="border-color:var(--w3-line);">
                <p>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
                <p>Made in India 🇮🇳</p>
            </div>
        </div>
    </footer>

    {{-- Video testimonial modal (reuses the global modal system) --}}
    <div id="w3-video-modal" data-modal class="d-none position-fixed top-0 start-0 w-100 h-100 z-3 align-items-center justify-content-center bg-black bg-opacity-75 p-4">
        <div class="position-relative w-100 mw-3xl">
            <button type="button" data-modal-close class="btn btn-link p-0 text-decoration-none position-absolute top-n10 end-0 text-white text-opacity-75 d-inline-flex align-items-center gap-1 fs-sm"><x-icon name="x-mark" class="w-5 h-5" /> Close</button>
            <div class="rounded-4 overflow-hidden shadow-lg bg-black aspect-video">
                <video id="w3-video" class="w-100 h-100" controls playsinline preload="none" poster="{{ $featuredStory['image'] }}">
                    <source src="{{ $videoSrc }}" type="video/mp4">
                </video>
            </div>
            <p class="text-center text-white text-opacity-50 fs-xs mt-3">Sample clip — replace with your real customer testimonial video.</p>
        </div>
    </div>

    <script>
        (function () {
            // Testimonial slider (scroll-snap + dots + autoplay)
            var slider = document.getElementById('w3-slider');
            if (slider && slider.children.length) {
                var cards = slider.children, dotsWrap = document.getElementById('w3-slider-dots'), idx = 0, timer;
                for (var i = 0; i < cards.length; i++) {
                    var d = document.createElement('button');
                    d.type = 'button'; d.className = 'w3-dot'; d.setAttribute('data-i', i);
                    dotsWrap.appendChild(d);
                }
                var dots = dotsWrap.children;
                function paint() { for (var i = 0; i < dots.length; i++) dots[i].classList.toggle('is-active', i === idx); }
                function go(n) { idx = (n + cards.length) % cards.length; slider.scrollTo({ left: cards[idx].offsetLeft - slider.offsetLeft, behavior: 'smooth' }); paint(); }
                function auto() { timer = setInterval(function () { go(idx + 1); }, 5000); }
                function stop() { clearInterval(timer); }
                document.querySelectorAll('[data-slider-next]').forEach(function (b) { b.addEventListener('click', function () { stop(); go(idx + 1); auto(); }); });
                document.querySelectorAll('[data-slider-prev]').forEach(function (b) { b.addEventListener('click', function () { stop(); go(idx - 1); auto(); }); });
                Array.prototype.forEach.call(dots, function (dot) { dot.addEventListener('click', function () { stop(); go(+this.getAttribute('data-i')); auto(); }); });
                slider.addEventListener('mouseenter', stop);
                slider.addEventListener('mouseleave', auto);
                var st;
                slider.addEventListener('scroll', function () {
                    clearTimeout(st);
                    st = setTimeout(function () {
                        var best = 0, min = Infinity;
                        for (var i = 0; i < cards.length; i++) { var dd = Math.abs(cards[i].offsetLeft - slider.offsetLeft - slider.scrollLeft); if (dd < min) { min = dd; best = i; } }
                        idx = best; paint();
                    }, 90);
                }, { passive: true });
                paint(); auto();
            }

            // Video modal: play (muted) on open, pause on close.
            var video = document.getElementById('w3-video');
            document.addEventListener('click', function (e) {
                if (e.target.closest('[data-video-open]')) {
                    setTimeout(function () { if (video) { video.muted = true; var p = video.play(); if (p && p.catch) p.catch(function () {}); } }, 60);
                } else if (e.target.closest('[data-modal-close]') || (e.target.matches && e.target.matches('#w3-video-modal'))) {
                    if (video) video.pause();
                }
            });
        })();
    </script>

    <script>
        // Sticky header: transparent over the hero, frosted once scrolled.
        (function () {
            var header = document.getElementById('w3-header');
            var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 40); };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            // Gentle mouse parallax on the hero's floating media.
            var media = document.getElementById('w3-hero-media');
            if (media && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
                media.addEventListener('mousemove', function (e) {
                    var r = media.getBoundingClientRect();
                    var x = (e.clientX - r.left) / r.width - 0.5;
                    var y = (e.clientY - r.top) / r.height - 0.5;
                    media.querySelectorAll('[data-depth]').forEach(function (el) {
                        var d = parseFloat(el.getAttribute('data-depth'));
                        el.style.transform = 'translate(' + (x * d) + 'px,' + (y * d) + 'px)';
                    });
                });
                media.addEventListener('mouseleave', function () {
                    media.querySelectorAll('[data-depth]').forEach(function (el) { el.style.transform = ''; });
                });
            }
        })();
    </script>
</body>
</html>
