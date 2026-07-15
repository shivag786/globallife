<x-layouts.public title="Your Wishlist">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 0); @endphp

    <div class="max-w-6xl mx-auto px-6 py-16">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-8">Your Wishlist</h1>

        @if ($items->isEmpty())
            <div class="text-center py-20 bg-white border border-slate-100 rounded-2xl">
                <x-icon name="heart" class="w-12 h-12 text-slate-300 mx-auto" />
                <p class="text-slate-500 mt-4">Your wishlist is empty.</p>
                <a href="{{ route('products.index') }}" class="inline-block mt-5 bg-brand-700 text-white px-6 py-2.5 rounded-full hover:bg-brand-800 transition">Browse products</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($items as $product)
                    <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow flex flex-col">
                        <a href="{{ route('products.show', $product) }}" class="aspect-square bg-gradient-to-b from-white to-brand-50/50 flex items-center justify-center overflow-hidden">
                            @if ($product->main_image)
                                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-5">
                            @endif
                        </a>
                        <div class="p-4 flex flex-col flex-1">
                            <a href="{{ route('products.show', $product) }}" class="font-display font-semibold text-brand-900 hover:text-brand-700 line-clamp-2">{{ $product->name }}</a>
                            <div class="mt-1">
                                @if ($product->hasPrice())
                                    <span class="text-lg font-bold text-brand-900">{{ $money($product->sellingPrice()) }}</span>
                                    @if ($product->hasDiscount())
                                        <span class="text-sm text-slate-400 line-through ml-1">{{ $money($product->mrp) }}</span>
                                    @endif
                                @else
                                    <span class="text-sm text-brand-700 font-medium">Enquire for price</span>
                                @endif
                            </div>
                            <div class="mt-auto pt-3 flex gap-2">
                                @if ($product->hasPrice())
                                    <form method="POST" action="{{ route('cart.add') }}" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full bg-brand-700 text-white text-sm py-2 rounded-full hover:bg-brand-800 transition">Add to Cart</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('wishlist.remove') }}">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="w-10 h-9 flex items-center justify-center border border-slate-200 rounded-full text-slate-400 hover:text-red-600 hover:border-red-200" aria-label="Remove">
                                        <x-icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.public>
