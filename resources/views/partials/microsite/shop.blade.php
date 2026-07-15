@php $storeProducts = $storeProducts ?? collect(); @endphp
@if ($storeProducts->isNotEmpty())
    <section id="shop" class="msite-section bg-white">
        <div class="msite-container">
            <div class="text-center max-w-xl mx-auto mb-10 reveal">
                <p class="text-xs font-heading font-bold uppercase tracking-[0.24em] text-brand-600">Shop</p>
                <h2 class="msite-heading text-3xl mt-2">Featured Products</h2>
                <p class="text-slate-500 mt-2">Genuine products, delivered to your door.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($storeProducts as $product)
                    <div class="reveal" style="transition-delay: {{ ($loop->index % 4) * 0.06 }}s">
                        <x-store-product-card :product="$product" :seller="$microsite" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
