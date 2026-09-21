<x-layouts.public :title="$category->name" :metaDescription="$category->meta_description ?? ('Shop '.$category->name.' at Global Life.')">
    <div class="max-w-6xl mx-auto px-6 py-16">
        <nav class="text-sm text-slate-500 mb-6 flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="hover:text-brand-700">All Products</a>
            <x-icon name="arrow-right" class="w-3.5 h-3.5" />
            <span class="text-slate-700 font-medium">{{ $category->name }}</span>
        </nav>

        @if ($category->image)
            <div class="relative rounded-2xl overflow-hidden mb-10 premium-shadow">
                <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                     class="w-full h-48 sm:h-64 object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-950/80 via-brand-950/30 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8 text-white">
                    <h1 class="font-display text-3xl sm:text-4xl font-bold">{{ $category->name }}</h1>
                    @if ($category->description)
                        <p class="mt-2 text-white/80 max-w-xl text-sm sm:text-base">{{ $category->description }}</p>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center mb-12">
                <h1 class="font-display text-4xl font-bold text-brand-900 mb-3">{{ $category->name }}</h1>
                @if ($category->description)
                    <p class="text-slate-500 max-w-xl mx-auto">{{ $category->description }}</p>
                @endif
            </div>
        @endif

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($products as $product)
                <x-store-product-card :product="$product" />
            @empty
                <p class="col-span-full text-center text-slate-400 py-16">No products in this category yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.public>
