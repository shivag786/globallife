@if ($microsite->services->isNotEmpty())
    <section id="services" class="msite-section bg-cream-dark/30">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">What We Offer</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Our Services</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($microsite->services as $index => $service)
                    <div class="msite-card msite-card-img overflow-hidden reveal" data-reveal="up" style="transition-delay: {{ ($index % 3) * 0.1 }}s">
                        <div class="relative msite-card-img h-52">
                            @if ($service->image_path)
                                <img src="{{ asset('storage/'.$service->image_path) }}" loading="lazy" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-brand-100 flex items-center justify-center">
                                    <x-icon name="sparkles" class="w-10 h-10 text-brand-400" />
                                </div>
                            @endif
                            @if ($service->show_pricing && $service->discount_percent)
                                <span class="absolute top-3 left-3 bg-gold-500 text-brand-950 text-xs font-bold px-3 py-1 rounded-full">
                                    {{ $service->discount_percent }}% OFF
                                </span>
                            @endif
                        </div>
                        <div class="p-6">
                            @if ($service->category)
                                <p class="text-xs font-semibold text-brand-500 uppercase tracking-wide mb-1">{{ $service->category }}</p>
                            @endif
                            <p class="font-heading font-bold text-lg text-brand-950">{{ $service->name }}</p>
                            @if ($service->short_description)
                                <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $service->short_description }}</p>
                            @endif

                            <div class="flex items-center justify-between mt-5">
                                @if ($service->show_pricing && ($service->offer_price || $service->mrp))
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl font-extrabold text-brand-700">₹{{ $service->offer_price ?? $service->mrp }}</span>
                                        @if ($service->offer_price && $service->mrp && $service->mrp > $service->offer_price)
                                            <span class="text-sm text-slate-400 line-through">₹{{ $service->mrp }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span></span>
                                @endif
                                @if ($service->show_book_now)
                                    <a href="{{ route('microsite.click', [$microsite, 'booking']) }}" class="msite-btn msite-btn-primary !h-10 !px-5 !text-sm">
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
