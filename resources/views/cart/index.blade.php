<x-layouts.shop title="Your Cart">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $storefront = app(\App\Services\StorefrontContext::class)->current();
        $continueUrl = app(\App\Services\StorefrontContext::class)->continueUrl($storefront);
    @endphp

    <div class="mw-5xl mx-auto px-4 px-sm-6 py-12 py-sm-16">
        <h1 class="font-display fs-2xl fs-3xl fw-bold text-brand-emphasis mb-8">Your Cart</h1>

        @if ($items->isEmpty())
            <div class="text-center py-20 bg-body border rounded-4">
                <x-icon name="shopping-bag" class="w-12 h-12 text-body-tertiary mx-auto" />
                <p class="text-secondary mt-4">Your cart is empty.</p>
                <a href="{{ route('products.index') }}" class="btn btn-brand rounded-pill d-inline-block mt-5">Browse products</a>
            </div>
        @else
            <div class="d-grid grid-template-lg gap-6 gap-lg-8 align-items-start" style="--cols-lg: minmax(0, 1fr) 340px">
                {{-- Line items --}}
                <div class="bg-body border rounded-4 divide-y">
                    @foreach ($items as $item)
                        <div data-cart-line data-key="{{ $item['key'] }}" class="d-flex gap-3 gap-sm-4 p-4 p-sm-5">
                            <a href="{{ route('products.show', $item['product']) }}" class="w-20 h-20 w-24 h-24 rounded-3 bg-body-tertiary d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0">
                                @if ($item['product']->main_image)
                                    <img src="{{ asset('storage/'.$item['product']->main_image) }}" alt="" class="w-100 h-100 object-fit-contain p-2">
                                @endif
                            </a>
                            <div class="flex-grow-1 min-w-0 d-flex flex-column">
                                <a href="{{ route('products.show', $item['product']) }}" class="fw-medium text-brand-emphasis lh-sm line-clamp-2">{{ $item['product']->name }}</a>
                                @if ($item['seller'])
                                    <p class="fs-xs text-secondary mt-1">Sold by {{ $item['seller']->business_name }}</p>
                                @endif
                                <p class="fs-sm text-secondary mt-1">{{ $money($item['unit_price']) }} each</p>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-auto pt-3">
                                    {{-- Quantity stepper --}}
                                    <div class="d-inline-flex align-items-center border rounded-pill">
                                        <form method="POST" action="{{ route('cart.update') }}" data-cart-ajax data-role="dec">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                            <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 d-flex align-items-center justify-content-center text-secondary rounded-start" aria-label="Decrease">
                                                <x-icon name="minus" class="w-4 h-4" />
                                            </button>
                                        </form>
                                        <span data-qty class="w-9 text-center fs-sm fw-semibold font-monospace">{{ $item['quantity'] }}</span>
                                        <form method="POST" action="{{ route('cart.update') }}" data-cart-ajax data-role="inc">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                            <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 d-flex align-items-center justify-content-center text-secondary rounded-end" aria-label="Increase">
                                                <x-icon name="plus" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span data-line-total class="fw-semibold text-brand-emphasis font-monospace">{{ $money($item['line_total']) }}</span>
                                        <form method="POST" action="{{ route('cart.remove') }}" data-cart-ajax data-role="remove">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <button type="submit" class="btn btn-link p-0 text-decoration-none w-9 h-9 d-flex align-items-center justify-content-center rounded-pill text-secondary" aria-label="Remove item" title="Remove">
                                                <x-icon name="trash" class="w-5 h-5" />
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary --}}
                <div class="bg-body border rounded-4 p-6 premium-shadow position-sticky top-24">
                    <h2 class="fw-semibold text-brand-emphasis mb-4">Order Summary</h2>

                    <dl class="d-flex flex-column gap-3 fs-sm border-top pt-4">
                        <div class="d-flex justify-content-between"><dt class="text-secondary">Subtotal</dt><dd class="fw-medium" data-summary-subtotal>{{ $money($totals['subtotal']) }}</dd></div>
                        <div class="d-flex justify-content-between">
                            <dt class="text-secondary">Shipping</dt>
                            <dd class="fw-medium" data-summary-shipping>{{ $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free' }}</dd>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-3 fs-base"><dt class="fw-semibold text-brand-emphasis">Total</dt><dd class="fw-bold text-brand-emphasis" data-summary-total>{{ $money($totals['total']) }}</dd></div>
                    </dl>

                    <a href="{{ route('checkout.index') }}" class="btn btn-brand rounded-pill mt-6 d-block text-center">Proceed to Checkout</a>
                    <a href="{{ $continueUrl }}" class="mt-3 d-flex align-items-center justify-content-center gap-2 fs-sm text-secondary">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        {{ $storefront ? 'Continue shopping at '.\Illuminate\Support\Str::limit($storefront->business_name, 22) : 'Continue shopping' }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.shop>
