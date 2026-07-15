@props(['product', 'seller' => null])

@php
    $money = fn ($n) => '₹'.number_format((float) $n, 0);
    $sellerId = $seller?->id;
    $detailUrl = route('products.show', $product).($sellerId ? '?store='.$sellerId : '');
    $rating = (int) round((float) $product->rating);
    $cart = app(\App\Services\CartService::class);
    $inCartQty = $cart->quantityFor($product->id, $sellerId);
    $cartKey = $cart->keyFor($product->id, $sellerId);
@endphp

<div class="group bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition flex flex-col h-full">
    <div class="relative aspect-square bg-gradient-to-b from-white to-brand-50/50 overflow-hidden">
        <a href="{{ $detailUrl }}" aria-label="{{ $product->name }}">
            @if ($product->main_image)
                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}"
                     class="w-full h-full object-contain p-5 group-hover:scale-105 transition duration-500">
            @endif
        </a>
        @if ($product->discountPercentage())
            <span class="absolute top-3 left-3 text-xs font-bold text-white px-2 py-1 rounded-full bg-green-600 shadow-sm">{{ $product->discountPercentage() }}% OFF</span>
        @endif
        @if ($inCartQty > 0)
            <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 text-xs font-semibold text-white px-2 py-1 rounded-full bg-brand-700 shadow-sm">
                <x-icon name="check-circle" class="w-3.5 h-3.5" /> In cart
            </span>
        @endif
        <form method="POST" action="{{ route('wishlist.add') }}" class="absolute top-3 right-3">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button type="submit" class="w-9 h-9 rounded-full bg-white/90 backdrop-blur shadow flex items-center justify-center text-slate-500 hover:text-rose-500 transition" aria-label="Save to wishlist">
                <x-icon name="heart" class="w-4 h-4" />
            </button>
        </form>
    </div>

    <div class="p-4 flex flex-col flex-1">
        @if ($product->category)
            <p class="text-[0.68rem] uppercase tracking-wide text-slate-400 mb-0.5">{{ $product->category }}</p>
        @endif
        <a href="{{ $detailUrl }}" class="font-display font-semibold text-brand-900 leading-tight hover:text-brand-700 line-clamp-2">{{ $product->name }}</a>

        @if ($product->review_count > 0)
            <div class="flex items-center gap-1 mt-1.5">
                <div class="flex">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-icon name="star" class="w-3.5 h-3.5 {{ $i <= $rating ? 'text-gold-500' : 'text-slate-300' }}" :filled="$i <= $rating" />
                    @endfor
                </div>
                <span class="text-xs text-slate-400">({{ $product->review_count }})</span>
            </div>
        @endif

        <div class="mt-2 flex items-baseline gap-2">
            @if ($product->hasPrice())
                <span class="text-lg font-bold text-brand-900">{{ $money($product->sellingPrice()) }}</span>
                @if ($product->hasDiscount())
                    <span class="text-sm text-slate-400 line-through">{{ $money($product->mrp) }}</span>
                @endif
            @else
                <span class="text-sm text-brand-700 font-medium">Enquire for price</span>
            @endif
        </div>

        <div class="mt-auto pt-3 space-y-2">
            @if ($product->hasPrice())
                <div class="flex gap-2">
                    @if ($inCartQty > 0)
                        {{-- Already in cart → quantity stepper --}}
                        <div class="flex-1 inline-flex items-center justify-between border border-brand-600 rounded-full overflow-hidden">
                            <form method="POST" action="{{ route('cart.update') }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="key" value="{{ $cartKey }}">
                                <input type="hidden" name="quantity" value="{{ $inCartQty - 1 }}">
                                <button type="submit" class="w-9 h-9 flex items-center justify-center text-brand-700 hover:bg-brand-50" aria-label="Decrease">
                                    <x-icon name="minus" class="w-4 h-4" />
                                </button>
                            </form>
                            <span class="text-sm font-semibold text-brand-700 tabular-nums">{{ $inCartQty }}</span>
                            <form method="POST" action="{{ route('cart.update') }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="key" value="{{ $cartKey }}">
                                <input type="hidden" name="quantity" value="{{ $inCartQty + 1 }}">
                                <button type="submit" class="w-9 h-9 flex items-center justify-center text-brand-700 hover:bg-brand-50" aria-label="Increase">
                                    <x-icon name="plus" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.add') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if ($sellerId) <input type="hidden" name="seller_id" value="{{ $sellerId }}"> @endif
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 bg-brand-700 text-white text-sm py-2 rounded-full hover:bg-brand-800 transition">
                                <x-icon name="shopping-bag" class="w-4 h-4" /> Add
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('cart.add') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="buy_now" value="1">
                        @if ($sellerId) <input type="hidden" name="seller_id" value="{{ $sellerId }}"> @endif
                        <button type="submit" class="w-full border border-brand-600 text-brand-700 text-sm py-2 rounded-full hover:bg-brand-50 transition">Buy Now</button>
                    </form>
                </div>
            @endif
            <div class="flex items-center justify-between text-xs text-slate-400">
                <x-product-benefits :product="$product" class="!px-3 !py-1.5 !text-xs !rounded-full" />
                <button type="button" data-copy-link="{{ url($detailUrl) }}" class="inline-flex items-center gap-1 hover:text-brand-600 transition" aria-label="Share">
                    <x-icon name="share" class="w-4 h-4" /> Share
                </button>
            </div>
        </div>
    </div>
</div>
