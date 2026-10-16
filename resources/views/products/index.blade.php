<x-layouts.public title="Our Products" :metaDescription="'Browse the full Global Life product range — wellness, nutrition, and fragrance.'">
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display fs-4xl fw-bold text-brand-emphasis mb-3">Our Product Range</h1>
            <p class="text-secondary mw-xl mx-auto">Every product, one promise of quality.</p>
        </div>

        <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-8">
            @forelse ($products as $product)
                <x-store-product-card :product="$product" />
            @empty
                <p class="col-span-full text-center text-secondary py-16">No products published yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.public>
