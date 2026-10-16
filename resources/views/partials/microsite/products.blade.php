@if ($microsite->products->isNotEmpty())
    <section id="products" class="msite-section">
        <div class="msite-container">
            <div class="text-center mw-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="fs-sm fw-semibold text-gold text-uppercase tracking-wide mb-2">Shop</p>
                <h2 class="msite-heading fs-3xl fs-4xl">Our Products</h2>
            </div>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
                @foreach ($microsite->products as $index => $product)
                    <div class="msite-card msite-card-img overflow-hidden reveal" style="transition-delay: {{ ($index % 4) * 0.1 }}s">
                        <div class="position-relative msite-card-img h-44">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" loading="lazy" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 bg-brand-subtle d-flex align-items-center justify-content-center">
                                    <x-icon name="tag" class="w-8 h-8 text-brand-" />
                                </div>
                            @endif
                            @if ($product->show_pricing && $product->discount_percent)
                                <span class="position-absolute top-0 end-3 bg-gold text-brand-emphasis fw-bold px-2 py-1 rounded-bottom">
                                    -{{ $product->discount_percent }}%
                                </span>
                            @endif
                        </div>
                        <div class="p-4">
                            @if ($product->brand)
                                <p class="fw-semibold text-brand text-uppercase tracking-wide">{{ $product->brand }}</p>
                            @endif
                            <p class="font-heading fw-semibold text-brand-emphasis fs-sm mt-1 line-clamp-2">{{ $product->name }}</p>
                            @if ($product->show_pricing && ($product->offer_price || $product->mrp))
                                <div class="d-flex align-items-baseline gap-2 mt-2">
                                    <span class="fw-bold text-brand">₹{{ $product->offer_price ?? $product->mrp }}</span>
                                    @if ($product->offer_price && $product->mrp && $product->mrp > $product->offer_price)
                                        <span class="fs-xs text-secondary text-decoration-line-through">₹{{ $product->mrp }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
