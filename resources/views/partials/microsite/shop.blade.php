@php $storeProducts = $storeProducts ?? collect(); @endphp
@if ($storeProducts->isNotEmpty())
    <section id="shop" class="msite-section bg-body">
        <div class="msite-container">
            <div class="text-center mw-xl mx-auto mb-10 reveal">
                <p class="fs-xs font-heading fw-bold text-uppercase tracking-widest text-brand">Shop</p>
                <h2 class="msite-heading fs-3xl mt-2">Featured Products</h2>
                <p class="text-secondary mt-2">Genuine products, delivered to your door.</p>
            </div>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-5">
                @foreach ($storeProducts as $product)
                    <div class="reveal" style="transition-delay: {{ ($loop->index % 4) * 0.06 }}s">
                        <x-store-product-card :product="$product" :seller="$microsite" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
