@if ($microsite->services->isNotEmpty())
    <section id="services" class="msite-section bg-cream">
        <div class="msite-container">
            <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">What We Offer</p>
                <h2 class="msite-heading fs-3xl fs-4xl">Our Services</h2>
            </div>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-8">
                @foreach ($microsite->services as $index => $service)
                    <div class="msite-card msite-card-img overflow-hidden reveal" data-reveal="up" style="transition-delay: {{ ($index % 3) * 0.1 }}s">
                        <div class="position-relative msite-card-img h-52">
                            @if ($service->image_path)
                                <img src="{{ asset('storage/'.$service->image_path) }}" loading="lazy" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 bg-brand-subtle d-flex align-items-center justify-content-center">
                                    <x-icon name="sparkles" class="w-10 h-10 text-brand" />
                                </div>
                            @endif
                            @if ($service->show_pricing && $service->discountPercentage())
                                <span class="position-absolute top-3 start-3 bg-gold text-brand-emphasis fs-xs fw-bold px-3 py-1 rounded-pill">
                                    {{ $service->discountPercentage() }}% OFF
                                </span>
                            @endif
                        </div>
                        <div class="p-6">
                            @if ($service->category)
                                <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mb-1">{{ $service->category }}</p>
                            @endif
                            <p class="font-heading fw-bold fs-lg text-brand-emphasis">{{ $service->name }}</p>
                            @if ($service->short_description)
                                <p class="fs-sm text-secondary mt-2 line-clamp-2">{{ $service->short_description }}</p>
                            @endif

                            <div class="d-flex align-items-center justify-content-between mt-5">
                                {{-- One price when only the MRP or only the sale price is set;
                                     sale price plus a struck-through MRP when both are. --}}
                                @if ($service->show_pricing && $service->hasPrice())
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fs-xl fw-bolder text-brand">
                                            ₹{{ number_format($service->sellingPrice(), 2) }}
                                        </span>
                                        @if ($service->strikeThroughPrice())
                                            <span class="fs-sm text-secondary text-decoration-line-through">
                                                ₹{{ number_format($service->strikeThroughPrice(), 2) }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span></span>
                                @endif
                                @if ($service->show_book_now)
                                    <a href="{{ route('microsite.click', [$microsite, 'booking']) }}" class="msite-btn msite-btn-primary h-10 px-5 fs-sm">
                                        Book Now
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
