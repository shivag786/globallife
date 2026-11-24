<x-layouts.public title="Your Wishlist">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 0); @endphp

    @php $businessItems = $businessItems ?? collect(); @endphp
    <div class="mw-6xl mx-auto px-4 px-sm-6 py-10 py-md-16">
        <h1 class="font-display fs-3xl fw-bold text-brand-emphasis mb-8">Your Wishlist</h1>

        @if ($items->isEmpty() && $businessItems->isEmpty())
            <div class="text-center py-20 bg-body border rounded-4">
                <x-icon name="heart" class="w-12 h-12 text-body-tertiary mx-auto" />
                <p class="text-secondary mt-4">Your wishlist is empty.</p>
                <a href="{{ route('products.index') }}" class="btn btn-brand rounded-pill d-inline-block mt-5">Browse products</a>
            </div>
        @else
            @if ($items->isNotEmpty())
            <div class="d-grid grid-cols-2 grid-cols-lg-4 gap-4 gap-md-6">
                @foreach ($items as $product)
                    <div class="bg-body border rounded-4 overflow-hidden premium-shadow d-flex flex-column">
                        <a href="{{ route('products.show', $product) }}" class="aspect-square d-flex align-items-center justify-content-center overflow-hidden" style="background: linear-gradient(to bottom, var(--bs-body-bg), color-mix(in srgb, var(--color-brand-50) 50%, transparent))">
                            @if ($product->main_image)
                                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-contain p-5">
                            @endif
                        </a>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <a href="{{ route('products.show', $product) }}" class="font-display fw-semibold text-brand-emphasis line-clamp-2">{{ $product->name }}</a>
                            <div class="mt-1">
                                @if ($product->hasPrice())
                                    <span class="fs-lg fw-bold text-brand-emphasis">{{ $money($product->sellingPrice()) }}</span>
                                    @if ($product->hasDiscount())
                                        <span class="fs-sm text-secondary text-decoration-line-through ms-1">{{ $money($product->mrp) }}</span>
                                    @endif
                                @else
                                    <span class="fs-sm text-brand fw-medium">Enquire for price</span>
                                @endif
                            </div>
                            <div class="mt-auto pt-3 d-flex gap-2">
                                @if ($product->hasPrice())
                                    <form method="POST" action="{{ route('cart.add') }}" class="flex-grow-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="btn btn-brand btn-sm rounded-pill w-100">Add to Cart</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('wishlist.remove') }}">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none w-10 h-9 d-flex align-items-center justify-content-center border rounded-pill text-secondary" aria-label="Remove">
                                        <x-icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Saved from VIP business pages (their own products). --}}
            @if ($businessItems->isNotEmpty())
                <h2 class="font-display fs-xl fw-bold text-brand-emphasis {{ $items->isNotEmpty() ? 'mt-12' : '' }} mb-5">From business pages</h2>
                <div class="d-grid grid-cols-2 grid-cols-lg-4 gap-4 gap-md-6">
                    @foreach ($businessItems as $product)
                        @php
                            $shop = $product->vipMicrosite;
                            $detailUrl = url($shop->productPath($product));
                        @endphp
                        <div class="bg-body border rounded-4 overflow-hidden premium-shadow d-flex flex-column">
                            <a href="{{ $detailUrl }}" class="aspect-square d-flex align-items-center justify-content-center overflow-hidden bg-brand-subtle">
                                @if ($product->image_path)
                                    <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <x-icon name="tag" class="w-10 h-10 text-brand" />
                                @endif
                            </a>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <a href="{{ url($shop->publicPath()) }}" class="fs-xs text-secondary text-decoration-none text-truncate">{{ $shop->business_name }}</a>
                                <a href="{{ $detailUrl }}" class="font-display fw-semibold text-brand-emphasis line-clamp-2">{{ $product->name }}</a>
                                @if ($product->show_pricing && $product->hasPrice())
                                    <span class="fs-lg fw-bold text-brand-emphasis mt-1">{{ $money($product->sellingPrice()) }}</span>
                                @endif
                                <div class="mt-auto pt-3 d-flex gap-2">
                                    <a href="{{ $detailUrl }}" class="btn btn-brand btn-sm rounded-pill flex-grow-1">View</a>
                                    <form method="POST" action="{{ route('wishlist.remove') }}">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="business_product_id" value="{{ $product->id }}">
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none w-10 h-9 d-flex align-items-center justify-content-center border rounded-pill text-secondary" aria-label="Remove">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-layouts.public>
