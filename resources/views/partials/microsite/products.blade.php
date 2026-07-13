@if ($microsite->products->isNotEmpty())
    <section id="products" class="msite-section">
        <div class="msite-container">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal" data-reveal="zoom">
                <p class="text-sm font-semibold text-gold-600 uppercase tracking-wide mb-2">Shop</p>
                <h2 class="msite-heading text-3xl md:text-4xl">Our Products</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($microsite->products as $index => $product)
                    <div class="msite-card msite-card-img overflow-hidden reveal" style="transition-delay: {{ ($index % 4) * 0.1 }}s">
                        <div class="relative msite-card-img h-44">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" loading="lazy" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-brand-100 flex items-center justify-center">
                                    <x-icon name="tag" class="w-8 h-8 text-brand-400" />
                                </div>
                            @endif
                            @if ($product->show_pricing && $product->discount_percent)
                                <span class="absolute top-0 right-3 bg-gold-500 text-brand-950 text-[11px] font-bold px-2.5 py-1 rounded-b-md">
                                    -{{ $product->discount_percent }}%
                                </span>
                            @endif
                        </div>
                        <div class="p-4">
                            @if ($product->brand)
                                <p class="text-[11px] font-semibold text-brand-500 uppercase tracking-wide">{{ $product->brand }}</p>
                            @endif
                            <p class="font-heading font-semibold text-brand-950 text-sm mt-0.5 line-clamp-2">{{ $product->name }}</p>
                            @if ($product->show_pricing && ($product->offer_price || $product->mrp))
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="font-bold text-brand-700">₹{{ $product->offer_price ?? $product->mrp }}</span>
                                    @if ($product->offer_price && $product->mrp && $product->mrp > $product->offer_price)
                                        <span class="text-xs text-slate-400 line-through">₹{{ $product->mrp }}</span>
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
