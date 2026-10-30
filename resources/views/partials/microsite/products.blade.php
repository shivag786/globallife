@if ($microsite->products->isNotEmpty())
    <section id="products" class="msite-section {{ $sectionBg ?? '' }}">
        <div class="msite-container">
            @include('partials.microsite.section-heading', ['heading' => $microsite->layout()->heading('products')])
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
                @foreach ($microsite->products as $index => $product)
                    <div class="reveal" style="transition-delay: {{ ($index % 4) * 0.1 }}s">
                        <x-microsite.business-product-card :product="$product" :microsite="$microsite" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
