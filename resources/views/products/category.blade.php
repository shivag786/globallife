<x-layouts.public :title="$category->name" :metaDescription="$category->meta_description ?? ('Shop '.$category->name.' at Global Life.')">
    <div class="mw-6xl mx-auto px-6 py-16">
        <nav class="fs-sm text-secondary mb-6 d-flex align-items-center gap-2">
            <a href="{{ route('products.index') }}" class="">All Products</a>
            <x-icon name="arrow-right" class="w-3.5 h-3.5" />
            <span class="text-body-secondary fw-medium">{{ $category->name }}</span>
        </nav>

        @if ($category->image)
            <div class="position-relative rounded-4 overflow-hidden mb-10 premium-shadow">
                <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                     class="w-100 h-48 h-64 object-fit-cover">
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to top, color-mix(in srgb, var(--color-brand-950) 80%, transparent), color-mix(in srgb, var(--color-brand-950) 30%, transparent), transparent)"></div>
                <div class="position-absolute start-0 end-0 bottom-0 p-6 p-sm-8 text-white">
                    <h1 class="font-display fs-3xl fs-4xl fw-bold">{{ $category->name }}</h1>
                    @if ($category->description)
                        <p class="mt-2 text-white text-opacity-75 mw-xl fs-sm fs-base">{{ $category->description }}</p>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center mb-12">
                <h1 class="font-display fs-4xl fw-bold text-brand-emphasis mb-3">{{ $category->name }}</h1>
                @if ($category->description)
                    <p class="text-secondary mw-xl mx-auto">{{ $category->description }}</p>
                @endif
            </div>
        @endif

        <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
            @forelse ($products as $product)
                <x-store-product-card :product="$product" />
            @empty
                <p class="col-span-full text-center text-secondary py-16">No products in this category yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.public>
