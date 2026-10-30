@props(['product', 'microsite'])

{{-- A business's OWN product on its public page. What the buttons do depends on
     the package (VipPlan::selling_mode):
       direct    → Add to cart / Buy now, paid through the company checkout;
       catalogue → Order on WhatsApp, with the order message pre-typed. --}}
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 0);
    $direct = $microsite->sellsDirect();
    $canBuy = $direct && $product->isPurchasable();
    $showPrice = $product->show_pricing && $product->hasPrice();
    $whatsapp = $direct ? null : $microsite->whatsappOrderUrl($product);
    $modalId = 'product-detail-'.$product->id;

    $cart = app(\App\Services\CartService::class);
    $cartKey = $cart->businessKeyFor($product->id, $microsite->id);
    $inCartQty = $canBuy ? $cart->businessQuantityFor($product->id, $microsite->id) : 0;
@endphp

<div id="product-{{ $product->id }}" data-product-card class="msite-card msite-card-img overflow-hidden d-flex flex-column h-100">
    <button type="button" data-modal-open="#{{ $modalId }}" class="btn btn-link p-0 text-decoration-none position-relative msite-card-img aspect-square d-block w-100 bg-brand-subtle">
        @if ($product->image_path)
            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" loading="lazy" class="w-100 h-100 object-fit-cover">
        @else
            <span class="w-100 h-100 d-flex align-items-center justify-content-center">
                <x-icon name="tag" class="w-10 h-10 text-brand" />
            </span>
        @endif
        @if ($showPrice && $product->discountPercentage())
            <span class="position-absolute top-3 start-3 bg-gold text-brand-emphasis fs-xs fw-bold px-3 py-1 rounded-pill">
                {{ $product->discountPercentage() }}% OFF
            </span>
        @endif
        @if (! $product->isInStock())
            <span class="position-absolute top-3 end-3 bg-dark text-white fs-xs fw-bold px-3 py-1 rounded-pill">Out of stock</span>
        @endif
    </button>

    <div class="p-4 d-flex flex-column flex-grow-1">
        @if ($product->category)
            <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mb-1">{{ $product->category }}</p>
        @endif
        <p class="font-heading fw-semibold text-brand-emphasis line-clamp-2">{{ $product->name }}</p>
        @if ($product->short_description)
            <p class="fs-sm text-secondary mt-1 line-clamp-2">{{ $product->short_description }}</p>
        @endif

        @if ($showPrice)
            <div class="d-flex align-items-baseline gap-2 mt-2">
                <span class="fs-lg fw-bold text-brand">{{ $money($product->sellingPrice()) }}</span>
                @if ($product->strikeThroughPrice())
                    <span class="fs-sm text-secondary text-decoration-line-through">{{ $money($product->strikeThroughPrice()) }}</span>
                @endif
            </div>
        @endif

        <div class="mt-auto pt-4 d-flex flex-column gap-2">
            @if ($canBuy)
                <div class="d-flex gap-2" data-cart-controls data-key="{{ $cartKey }}">
                    <form method="POST" action="{{ route('cart.add') }}" class="flex-grow-1 {{ $inCartQty > 0 ? 'd-none' : '' }}" data-cart-ajax data-role="add">
                        @csrf
                        <input type="hidden" name="business_product_id" value="{{ $product->id }}">
                        <button type="submit" class="btn btn-brand btn-sm rounded-pill w-100 d-inline-flex align-items-center justify-content-center gap-2">
                            <x-icon name="shopping-bag" class="w-4 h-4" /> Add to cart
                        </button>
                    </form>
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
                    <form method="POST" action="{{ route('cart.add') }}" class="flex-grow-1">
                        @csrf
                        <input type="hidden" name="business_product_id" value="{{ $product->id }}">
                        <input type="hidden" name="buy_now" value="1">
                        <button type="submit" class="btn btn-link p-0 text-decoration-none w-100 border border-brand text-brand fs-sm py-2 rounded-pill">Buy Now</button>
                    </form>
                </div>
            @elseif ($direct)
                <span class="btn btn-sm rounded-pill border text-secondary disabled">
                    {{ $product->isInStock() ? 'Not available online' : 'Out of stock' }}
                </span>
            @elseif ($whatsapp)
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                   class="msite-btn msite-btn-whatsapp msite-btn-sm w-100 justify-content-center">
                    <x-icon name="chat-bubble" class="w-4 h-4" /> Order on WhatsApp
                </a>
            @else
                <a href="#enquiry" class="btn btn-brand btn-sm rounded-pill w-100">Enquire to order</a>
            @endif

            <button type="button" data-modal-open="#{{ $modalId }}" class="btn btn-link p-0 fs-sm text-brand text-decoration-none">
                View details
            </button>
        </div>
    </div>
</div>

<x-microsite.detail-modal :id="$modalId" :title="$product->name" :subtitle="$product->category"
                          :image="$product->image_path" :short="$product->short_description" :long="$product->long_description">
    @if ($showPrice)
        <x-slot:price>
            <span class="fs-xl fw-bold text-brand">{{ $money($product->sellingPrice()) }}</span>
            @if ($product->strikeThroughPrice())
                <span class="fs-sm text-secondary text-decoration-line-through ms-2">{{ $money($product->strikeThroughPrice()) }}</span>
            @endif
        </x-slot:price>
    @endif
    @if ($canBuy)
        <form method="POST" action="{{ route('cart.add') }}">
            @csrf
            <input type="hidden" name="business_product_id" value="{{ $product->id }}">
            <input type="hidden" name="buy_now" value="1">
            <button type="submit" class="msite-btn msite-btn-primary">Buy Now</button>
        </form>
    @elseif ($whatsapp)
        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="msite-btn msite-btn-whatsapp">Order on WhatsApp</a>
    @endif
</x-microsite.detail-modal>
