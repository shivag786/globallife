@php
    // One of a VIP business's services (BusinessService), on its own page.
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
    $showPrice = $service->show_pricing && $service->hasPrice();
    $homeUrl = url($microsite->publicPath());
    $tags = array_filter((array) $service->tags);
    $whatsappNumber = $microsite->orderWhatsappNumber();
    $whatsappUrl = $whatsappNumber
        ? 'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode("Hello {$microsite->business_name}, I would like to know more about: {$service->name}\n\n".url($microsite->servicePath($service)))
        : null;

    $navSections = array_filter([
        'services' => 'Services',
        'products' => $microsite->isModuleVisible('products') ? 'Products' : null,
        'enquiry' => 'Contact',
    ]);
@endphp
<x-layouts.microsite :title="($service->meta_title ?: $service->name).' · '.$microsite->business_name" :microsite="$microsite"
                     :nav-sections="$navSections" :meta-description="$service->meta_description ?: $service->short_description"
                     :og-image="$service->image_path">
    <section class="msite-section pt-6 pt-md-10">
        <div class="msite-container">
            <nav aria-label="breadcrumb" class="fs-sm mb-6">
                <ol class="breadcrumb mb-0 flex-nowrap text-truncate">
                    <li class="breadcrumb-item"><a href="{{ $homeUrl }}" class="text-brand text-decoration-none">{{ $microsite->business_name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ $homeUrl }}#services" class="text-brand text-decoration-none">Services</a></li>
                    <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $service->name }}</li>
                </ol>
            </nav>

            <div class="row g-5 g-lg-10 align-items-start">
                <div class="col-12 col-md-6">
                    <div class="msite-card overflow-hidden position-relative bg-brand-subtle" style="aspect-ratio: 4 / 3">
                        @if ($service->image_path)
                            <img src="{{ asset('storage/'.$service->image_path) }}" alt="{{ $service->name }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <span class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <x-icon name="sparkles" class="w-16 h-16 text-brand" />
                            </span>
                        @endif
                        @if ($showPrice && $service->discountPercentage())
                            <span class="position-absolute top-3 start-3 bg-gold text-brand-emphasis fs-xs fw-bold px-3 py-1 rounded-pill">
                                {{ $service->discountPercentage() }}% OFF
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    @if ($service->category)
                        <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mb-2">{{ $service->category }}</p>
                    @endif
                    <h1 class="msite-heading fs-3xl mb-3 text-break">{{ $service->name }}</h1>

                    @if ($showPrice)
                        <div class="d-flex flex-wrap align-items-baseline gap-2 mb-4">
                            <span class="fs-2xl fw-bold text-brand">{{ $money($service->sellingPrice()) }}</span>
                            @if ($service->strikeThroughPrice())
                                <span class="fs-lg text-secondary text-decoration-line-through">{{ $money($service->strikeThroughPrice()) }}</span>
                            @endif
                        </div>
                    @endif

                    @if ($service->short_description)
                        <p class="fs-lg text-body-secondary mb-5">{{ $service->short_description }}</p>
                    @endif

                    <div class="d-flex flex-wrap gap-3 mb-5">
                        @if ($service->show_book_now)
                            <a href="{{ route('microsite.click', [$microsite, 'booking']) }}" class="msite-btn msite-btn-primary">
                                <x-icon name="calendar" class="w-4 h-4" /> Book Now
                            </a>
                        @endif
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="msite-btn msite-btn-whatsapp">
                                <x-whatsapp-icon class="w-5 h-5" /> Ask on WhatsApp
                            </a>
                        @endif
                        @if ($microsite->phone_number)
                            <a href="{{ route('microsite.click', [$microsite, 'call']) }}" class="msite-btn border border-2 border-brand text-brand bg-transparent">
                                <x-icon name="phone" class="w-4 h-4" /> Call
                            </a>
                        @endif
                    </div>

                    @if ($tags)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <span class="fs-xs px-3 py-1 rounded-pill bg-brand-subtle text-brand">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($service->long_description)
                <div class="msite-card p-6 p-md-8 mt-10">
                    <h2 class="msite-heading fs-xl mb-4">About this service</h2>
                    <div class="text-body-secondary text-prewrap text-break">{{ $service->long_description }}</div>
                </div>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="msite-section bg-cream">
            <div class="msite-container">
                <h2 class="msite-heading fs-2xl mb-6">Other services</h2>
                <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-6">
                    @foreach ($related as $other)
                        <a href="{{ url($microsite->servicePath($other)) }}" class="msite-card overflow-hidden text-decoration-none d-block">
                            <div class="h-44 bg-brand-subtle">
                                @if ($other->image_path)
                                    <img src="{{ asset('storage/'.$other->image_path) }}" alt="{{ $other->name }}" loading="lazy" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="w-100 h-100 d-flex align-items-center justify-content-center"><x-icon name="sparkles" class="w-10 h-10 text-brand" /></span>
                                @endif
                            </div>
                            <div class="p-5">
                                <p class="font-heading fw-bold text-brand-emphasis mb-1">{{ $other->name }}</p>
                                @if ($other->show_pricing && $other->hasPrice())
                                    <span class="fw-bold text-brand">{{ $money($other->sellingPrice()) }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="enquiry" class="msite-section pt-0">
        <div class="msite-container mw-lg">
            <div class="msite-card p-6 p-md-8">
                <h2 class="msite-heading fs-2xl mb-5">Enquire about this service</h2>
                @include('partials.enquiry-form', [
                    'source' => 'microsite',
                    'vipMicrositeId' => $microsite->id,
                    'prefillCity' => $microsite->city->name,
                ])
            </div>
        </div>
    </section>
</x-layouts.microsite>
