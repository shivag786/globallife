<x-layouts.shop title="Your Cart">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $storefront = app(\App\Services\StorefrontContext::class)->current();
        $continueUrl = app(\App\Services\StorefrontContext::class)->continueUrl($storefront);
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-brand-900 mb-8">Your Cart</h1>

        @if ($items->isEmpty())
            <div class="text-center py-20 bg-white border border-slate-100 rounded-2xl">
                <x-icon name="shopping-bag" class="w-12 h-12 text-slate-300 mx-auto" />
                <p class="text-slate-500 mt-4">Your cart is empty.</p>
                <a href="{{ route('products.index') }}" class="inline-block mt-5 bg-brand-700 text-white px-6 py-2.5 rounded-full hover:bg-brand-800 transition">Browse products</a>
            </div>
        @else
            <div class="grid lg:grid-cols-[1fr_340px] gap-6 lg:gap-8 items-start">
                {{-- Line items --}}
                <div class="bg-white border border-slate-100 rounded-2xl divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <div data-cart-line data-key="{{ $item['key'] }}" class="flex gap-3 sm:gap-4 p-4 sm:p-5">
                            <a href="{{ route('products.show', $item['product']) }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-slate-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                                @if ($item['product']->main_image)
                                    <img src="{{ asset('storage/'.$item['product']->main_image) }}" alt="" class="w-full h-full object-contain p-1.5">
                                @endif
                            </a>
                            <div class="flex-1 min-w-0 flex flex-col">
                                <a href="{{ route('products.show', $item['product']) }}" class="font-medium text-brand-900 hover:text-brand-700 leading-snug line-clamp-2">{{ $item['product']->name }}</a>
                                @if ($item['seller'])
                                    <p class="text-xs text-slate-400 mt-0.5">Sold by {{ $item['seller']->business_name }}</p>
                                @endif
                                <p class="text-sm text-slate-500 mt-0.5">{{ $money($item['unit_price']) }} each</p>

                                <div class="flex flex-wrap items-center justify-between gap-3 mt-auto pt-3">
                                    {{-- Quantity stepper --}}
                                    <div class="inline-flex items-center border border-slate-200 rounded-full">
                                        <form method="POST" action="{{ route('cart.update') }}" data-cart-ajax data-role="dec">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-brand-700 hover:bg-slate-50 rounded-l-full" aria-label="Decrease">
                                                <x-icon name="minus" class="w-4 h-4" />
                                            </button>
                                        </form>
                                        <span data-qty class="w-9 text-center text-sm font-semibold tabular-nums">{{ $item['quantity'] }}</span>
                                        <form method="POST" action="{{ route('cart.update') }}" data-cart-ajax data-role="inc">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center text-slate-500 hover:text-brand-700 hover:bg-slate-50 rounded-r-full" aria-label="Increase">
                                                <x-icon name="plus" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span data-line-total class="font-semibold text-brand-900 tabular-nums">{{ $money($item['line_total']) }}</span>
                                        <form method="POST" action="{{ route('cart.remove') }}" data-cart-ajax data-role="remove">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-red-600 hover:bg-red-50 transition" aria-label="Remove item" title="Remove">
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
                <div class="bg-white border border-slate-100 rounded-2xl p-6 premium-shadow lg:sticky lg:top-24">
                    <h2 class="font-semibold text-brand-900 mb-4">Order Summary</h2>

                    <dl class="space-y-2.5 text-sm border-t border-slate-100 pt-4">
                        <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium" data-summary-subtotal>{{ $money($totals['subtotal']) }}</dd></div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Shipping</dt>
                            <dd class="font-medium" data-summary-shipping>{{ $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free' }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base"><dt class="font-semibold text-brand-900">Total</dt><dd class="font-bold text-brand-900" data-summary-total>{{ $money($totals['total']) }}</dd></div>
                    </dl>

                    <a href="{{ route('checkout.index') }}" class="mt-6 block text-center bg-brand-700 text-white py-3 rounded-full font-medium hover:bg-brand-800 transition">Proceed to Checkout</a>
                    <a href="{{ $continueUrl }}" class="mt-3 flex items-center justify-center gap-1.5 text-sm text-slate-500 hover:text-brand-700 transition">
                        <x-icon name="arrow-right" class="w-4 h-4 rotate-180" />
                        {{ $storefront ? 'Continue shopping at '.\Illuminate\Support\Str::limit($storefront->business_name, 22) : 'Continue shopping' }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.shop>
