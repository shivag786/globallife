<x-layouts.public title="Our Products" :metaDescription="'Browse the full Global Life product range — wellness, nutrition, and fragrance.'">
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl font-bold text-brand-900 mb-3">Our Product Range</h1>
            <p class="text-slate-500 max-w-xl mx-auto">Every product, one promise of quality.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($products as $index => $product)
                <a href="{{ route('products.show', $product) }}"
                   class="reveal group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition"
                   style="transition-delay: {{ ($index % 6) * 0.08 }}s">
                    <div class="aspect-[4/3] bg-brand-50 overflow-hidden">
                        @if ($product->main_image)
                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-6">
                        @if ($product->badge)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-gold-500/10 text-gold-600 px-2 py-1 rounded-full mb-2">
                                <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
                            </span>
                        @endif
                        <h2 class="font-display text-lg font-semibold text-brand-900 mb-1">{{ $product->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $product->short_description }}</p>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-slate-400 py-16">No products published yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.public>
