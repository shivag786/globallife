<x-layouts.public title="Our Products" :metaDescription="'Browse the full Global Life product range — wellness, nutrition, and fragrance.'">
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl font-bold text-brand-900 mb-3">Our Product Range</h1>
            <p class="text-slate-500 max-w-xl mx-auto">Every product, one promise of quality.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($products as $index => $product)
                <x-product-card :product="$product" :index="$index" />
            @empty
                <p class="col-span-full text-center text-slate-400 py-16">No products published yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.public>
