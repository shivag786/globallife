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

<div data-product-card class="bg-body border rounded-4 overflow-hidden premium-shadow d-flex flex-column h-100">
    <div class="position-relative aspect-square overflow-hidden" style="background: linear-gradient(to bottom, var(--bs-body-bg), color-mix(in srgb, var(--color-brand-50) 50%, transparent))">
        <a href="{{ $detailUrl }}" aria-label="{{ $product->name }}">
            @if ($product->main_image)
                <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}"
                     class="w-100 h-100 object-fit-contain p-5">
            @endif
        </a>
        @if ($product->discountPercentage())
            <span class="position-absolute top-3 start-3 fs-xs fw-bold text-white px-2 py-1 rounded-pill bg-success shadow-sm">{{ $product->discountPercentage() }}% OFF</span>
        @endif
        <form method="POST" action="{{ route('wishlist.add') }}" class="position-absolute top-3 end-3">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 rounded-pill bg-white bg-opacity-100 shadow-sm d-flex align-items-center justify-content-center text-secondary" aria-label="Save to wishlist">
                <x-icon name="heart" class="w-4 h-4" />
            </button>
        </form>
    </div>

    <div class="p-4 d-flex flex-column flex-grow-1">
        @if ($product->category)
            <p class="text-uppercase tracking-wide text-secondary mb-1">{{ $product->category }}</p>
        @endif
        <a href="{{ $detailUrl }}" class="font-display fw-semibold text-brand-emphasis lh-sm line-clamp-2">{{ $product->name }}</a>

        @if ($product->review_count > 0)
            <div class="d-flex align-items-center gap-1 mt-2">
                <div class="d-flex">
                    @for ($i = 1; $i <= 5; $i++)
                        <x-icon name="star" class="w-3.5 h-3.5 {{ $i <= $rating ? 'text-gold' : 'text-body-tertiary' }}" :filled="$i <= $rating" />
                    @endfor
                </div>
                <span class="fs-xs text-secondary">({{ $product->review_count }})</span>
            </div>
        @endif

        <div class="mt-2 d-flex align-items-baseline gap-2">
            @if ($product->hasPrice())
                <span class="fs-lg fw-bold text-brand-emphasis">{{ $money($product->sellingPrice()) }}</span>
                @if ($product->hasDiscount())
                    <span class="fs-sm text-secondary text-decoration-line-through">{{ $money($product->mrp) }}</span>
                @endif
            @else
                <span class="fs-sm text-brand fw-medium">Enquire for price</span>
            @endif
        </div>

        <div class="mt-auto pt-3 d-flex flex-column gap-2">
            @if ($product->hasPrice())
                <div class="d-flex gap-2" data-cart-controls data-key="{{ $cartKey }}">
                    {{-- Add button (shown when not in cart) --}}
                    <form method="POST" action="{{ route('cart.add') }}" class="flex-grow-1 {{ $inCartQty > 0 ? 'd-none' : '' }}" data-cart-ajax data-role="add">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if ($sellerId) <input type="hidden" name="seller_id" value="{{ $sellerId }}"> @endif
                        <button type="submit" class="btn btn-brand btn-sm rounded-pill w-100 d-inline-flex align-items-center justify-content-center gap-2">
                            <x-icon name="shopping-bag" class="w-4 h-4" /> Add
                        </button>
                    </form>
                    {{-- Quantity stepper (shown when in cart) --}}
                    <div class="flex-grow-1 d-inline-flex align-items-center justify-content-between border border-brand rounded-pill overflow-hidden {{ $inCartQty > 0 ? '' : 'd-none' }}" data-role="stepper">
                        <form method="POST" action="{{ route('cart.update') }}" data-cart-ajax data-role="dec">
                            @csrf @method('PATCH')
                            <input type="hidden" name="key" value="{{ $cartKey }}">
                            <input type="hidden" name="quantity" value="{{ $inCartQty - 1 }}">
                            <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 d-flex align-items-center justify-content-center text-brand" aria-label="Decrease">
                                <x-icon name="minus" class="w-4 h-4" />
                            </button>
                        </form>
                        <span data-qty class="fs-sm fw-semibold text-brand font-monospace">{{ max($inCartQty, 1) }}</span>
                        <form method="POST" action="{{ route('cart.update') }}" data-cart-ajax data-role="inc">
                            @csrf @method('PATCH')
                            <input type="hidden" name="key" value="{{ $cartKey }}">
                            <input type="hidden" name="quantity" value="{{ $inCartQty + 1 }}">
                            <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 d-flex align-items-center justify-content-center text-brand" aria-label="Increase">
                                <x-icon name="plus" class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                    {{-- Buy Now (navigates to checkout — intentionally not AJAX) --}}
                    <form method="POST" action="{{ route('cart.add') }}" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="buy_now" value="1">
                        @if ($sellerId) <input type="hidden" name="seller_id" value="{{ $sellerId }}"> @endif
                        <button type="submit" class="btn btn-link p-0 text-decoration-none w-100 border border-brand text-brand fs-sm py-2 rounded-pill">Buy Now</button>
                    </form>
                </div>
            @endif
            <div class="d-flex align-items-center justify-content-between fs-xs text-secondary">
                <x-product-benefits :product="$product" class="px-3 py-2 fs-xs rounded-pill" />
                <button type="button" data-copy-link="{{ url($detailUrl) }}" class="btn btn-link p-0 text-decoration-none d-inline-flex align-items-center gap-1" aria-label="Share">
                    <x-icon name="share" class="w-4 h-4" /> Share
                </button>
            </div>
        </div>
    </div>
</div>
