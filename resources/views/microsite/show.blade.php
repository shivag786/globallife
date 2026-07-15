@php
    $navSections = [];

    // Catalog products this VIP has enabled for sale (with active benefits eager-loaded).
    $storeProducts = $microsite->visibleCatalogProducts();
    $storeProducts->load(['benefits' => fn ($q) => $q->where('status', 'active')->orderBy('display_order')]);
    if ($storeProducts->isNotEmpty()) {
        $navSections['shop'] = 'Shop';
    }

    if ($microsite->isModuleVisible('about') && ($microsite->description || $microsite->short_description)) {
        $navSections['about'] = 'About';
    }
    if ($microsite->isModuleVisible('services') && $microsite->services->isNotEmpty()) {
        $navSections['services'] = 'Services';
    }
    if ($microsite->isModuleVisible('products') && $microsite->products->isNotEmpty()) {
        $navSections['products'] = 'Products';
    }
    if ($microsite->isModuleVisible('gallery') && $microsite->galleryItems->isNotEmpty()) {
        $navSections['gallery'] = 'Gallery';
    }
    if ($microsite->isModuleVisible('videos') && $microsite->videos->isNotEmpty()) {
        $navSections['videos'] = 'Videos';
    }
    if ($microsite->isModuleVisible('reviews')) {
        $navSections['reviews'] = 'Reviews';
    }
    if ($microsite->isModuleVisible('faqs') && $microsite->faqs->isNotEmpty()) {
        $navSections['faqs'] = 'FAQs';
    }
    if ($microsite->isModuleVisible('contact')) {
        $navSections['contact'] = 'Contact';
    }
@endphp
<x-layouts.microsite :title="$microsite->business_name" :microsite="$microsite" :nav-sections="$navSections">
    @if ($microsite->isModuleVisible('banner'))
        @include('partials.microsite.banner', ['banners' => $microsite->banners])
    @endif

    @if ($microsite->isModuleVisible('about'))
        @include('partials.microsite.about')
    @endif

    @include('partials.microsite.shop')

    @if ($microsite->isModuleVisible('services'))
        @include('partials.microsite.services')
    @endif

    @if ($microsite->isModuleVisible('products'))
        @include('partials.microsite.products')
    @endif

    @if ($microsite->isModuleVisible('gallery'))
        @include('partials.microsite.gallery')
    @endif

    @if ($microsite->isModuleVisible('videos'))
        @include('partials.microsite.videos')
    @endif

    @include('partials.microsite.reviews')

    @if ($microsite->isModuleVisible('faqs'))
        @include('partials.microsite.faqs')
    @endif

    @if ($microsite->isModuleVisible('contact'))
        @include('partials.microsite.contact')
    @endif

    @if ($microsite->isModuleVisible('social_media'))
        @include('partials.microsite.social')
    @endif

    <section id="enquiry" class="msite-section pt-0">
        <div class="msite-container max-w-lg">
            <div class="msite-card p-8 reveal">
                <h2 class="msite-heading text-2xl mb-5">Get in Touch</h2>
                @include('partials.enquiry-form', [
                    'source' => 'microsite',
                    'vipMicrositeId' => $microsite->id,
                    'prefillCity' => $microsite->city->name,
                ])
            </div>
        </div>
    </section>
</x-layouts.microsite>
