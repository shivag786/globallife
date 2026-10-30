@php $storeProducts = $storeProducts ?? collect(); @endphp
@if ($storeProducts->isNotEmpty())
    <section id="shop" class="msite-section {{ $sectionBg ?? 'bg-body' }}">
        <div class="msite-container">
            @include('partials.microsite.section-heading', ['heading' => $microsite->layout()->heading('shop')])
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
