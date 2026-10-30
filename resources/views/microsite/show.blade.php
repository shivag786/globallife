@php
    // Catalog products this VIP has enabled for sale (with active benefits eager-loaded).
    $storeProducts = $microsite->visibleCatalogProducts();
    $storeProducts->load(['benefits' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')]);

    $layout = $microsite->layout();
    $customSections = $microsite->customSections->keyBy(fn ($section) => $section->layoutKey());

    // Whether each section has anything to show. Sections render in the order
    // the owner set on the page builder; the hero banner stays first and the
    // enquiry form last.
    $renders = [
        'about' => $microsite->isModuleVisible('about') && ($microsite->description || $microsite->short_description),
        'stats' => $microsite->isModuleVisible('stats') && $layout->hasCustomStats(),
        'shop' => $microsite->isModuleVisible('shop') && $storeProducts->isNotEmpty(),
        'services' => $microsite->isModuleVisible('services') && $microsite->services->isNotEmpty(),
        'products' => $microsite->isModuleVisible('products') && $microsite->products->isNotEmpty(),
        'gallery' => $microsite->isModuleVisible('gallery') && $microsite->galleryItems->isNotEmpty(),
        'videos' => $microsite->isModuleVisible('videos') && $microsite->videos->isNotEmpty(),
        'reviews' => $microsite->isModuleVisible('reviews'),
        'faqs' => $microsite->isModuleVisible('faqs') && $microsite->faqs->isNotEmpty(),
        'contact' => $microsite->isModuleVisible('contact'),
        'social_media' => $microsite->isModuleVisible('social_media'),
    ];
    foreach ($customSections as $key => $section) {
        $renders[$key] = $section->is_active && $section->cards->isNotEmpty();
    }

    $order = array_values(array_filter($layout->order($microsite->customSections), fn ($key) => $renders[$key] ?? false));

    // Menu entries in page order. Built-ins keep their short menu names; an
    // owner's own section is listed under its title.
    $menu = [
        'about' => ['about', 'About'], 'stats' => ['highlights', 'Highlights'], 'shop' => ['shop', 'Shop'],
        'services' => ['services', 'Services'], 'products' => ['products', 'Products'],
        'gallery' => ['gallery', 'Gallery'], 'videos' => ['videos', 'Videos'], 'reviews' => ['reviews', 'Reviews'],
        'faqs' => ['faqs', 'FAQs'], 'contact' => ['contact', 'Contact'],
    ];
    $navSections = [];
    foreach ($order as $key) {
        if (isset($customSections[$key])) {
            $navSections['section-'.$customSections[$key]->id] = \Illuminate\Support\Str::limit($customSections[$key]->title, 18);
        } elseif (isset($menu[$key])) {
            $navSections[$menu[$key][0]] = $menu[$key][1];
        }
    }

    // Alternate plain and cream backgrounds down the page, whatever the order.
    $tone = 0;
@endphp
<x-layouts.microsite :title="$microsite->business_name" :microsite="$microsite" :nav-sections="$navSections">
    @if ($microsite->isModuleVisible('banner'))
        @include('partials.microsite.banner', ['banners' => $microsite->banners])
    @endif

    @foreach ($order as $key)
        @php $sectionBg = $key === 'stats' ? '' : (($tone++ % 2) ? 'bg-cream' : ''); @endphp
        @switch($key)
            @case('about') @include('partials.microsite.about') @break
            @case('stats') @include('partials.microsite.stats') @break
            @case('shop') @include('partials.microsite.shop') @break
            @case('services') @include('partials.microsite.services') @break
            @case('products') @include('partials.microsite.products') @break
            @case('gallery') @include('partials.microsite.gallery') @break
            @case('videos') @include('partials.microsite.videos') @break
            @case('reviews') @include('partials.microsite.reviews') @break
            @case('faqs') @include('partials.microsite.faqs') @break
            @case('contact') @include('partials.microsite.contact') @break
            @case('social_media') @include('partials.microsite.social') @break
            @default
                @include('partials.microsite.custom-section', ['section' => $customSections[$key]])
        @endswitch
    @endforeach

    <section id="enquiry" class="msite-section pt-0">
        <div class="msite-container mw-lg">
            <div class="msite-card p-8 reveal">
                <h2 class="msite-heading fs-2xl mb-5">Get in Touch</h2>
                @include('partials.enquiry-form', [
                    'source' => 'microsite',
                    'vipMicrositeId' => $microsite->id,
                    'prefillCity' => $microsite->city->name,
                ])
            </div>
        </div>
    </section>
</x-layouts.microsite>
