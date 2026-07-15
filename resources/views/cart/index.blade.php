<x-layouts.public title="Your Cart">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <div class="max-w-5xl mx-auto px-6 py-16">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-8">Your Cart</h1>

        @if ($items->isEmpty())
            <div class="text-center py-20 bg-white border border-slate-100 rounded-2xl">
                <x-icon name="tag" class="w-12 h-12 text-slate-300 mx-auto" />
                <p class="text-slate-500 mt-4">Your cart is empty.</p>
                <a href="{{ route('products.index') }}" class="inline-block mt-5 bg-brand-700 text-white px-6 py-2.5 rounded-full hover:bg-brand-800 transition">Browse products</a>
            </div>
        @else
            <div class="grid lg:grid-cols-[1fr_340px] gap-8 items-start">
                {{-- Line items --}}
                <div class="bg-white border border-slate-100 rounded-2xl divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <div class="flex gap-4 p-5">
                            <a href="{{ route('products.show', $item['product']) }}" class="w-20 h-20 rounded-xl bg-slate-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                                @if ($item['product']->main_image)
                                    <img src="{{ asset('storage/'.$item['product']->main_image) }}" alt="" class="w-full h-full object-contain p-1.5">
                                @endif
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('products.show', $item['product']) }}" class="font-medium text-brand-900 hover:text-brand-700">{{ $item['product']->name }}</a>
                                @if ($item['seller'])
                                    <p class="text-xs text-slate-400 mt-0.5">Sold by {{ $item['seller']->business_name }}{{ $item['seller']->city ? ', '.$item['seller']->city->name : '' }}</p>
                                @endif
                                <p class="text-sm text-slate-500 mt-1">{{ $money($item['unit_price']) }} each</p>

                                <div class="flex items-center gap-4 mt-3">
                                    {{-- Quantity stepper --}}
                                    <div class="inline-flex items-center border border-slate-200 rounded-full">
                                        <form method="POST" action="{{ route('cart.update') }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-brand-700" aria-label="Decrease">
                                                <x-icon name="minus" class="w-4 h-4" />
                                            </button>
                                        </form>
                                        <span class="w-8 text-center text-sm font-medium tabular-nums">{{ $item['quantity'] }}</span>
                                        <form method="POST" action="{{ route('cart.update') }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-brand-700" aria-label="Increase">
                                                <x-icon name="plus" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('cart.remove') }}">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-red-600 hover:bg-red-50 transition" aria-label="Remove item" title="Remove">
                                            <x-icon name="trash" class="w-4.5 h-4.5" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="text-right font-semibold text-brand-900 whitespace-nowrap">{{ $money($item['line_total']) }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary --}}
                <div class="bg-white border border-slate-100 rounded-2xl p-6 premium-shadow">
                    <h2 class="font-semibold text-brand-900 mb-4">Order Summary</h2>

                    <div class="flex items-center gap-2 mb-4">
                        <input type="text" placeholder="Coupon code" disabled
                               class="flex-1 rounded-md border-slate-200 bg-slate-50 text-sm text-slate-400 cursor-not-allowed">
                        <button type="button" disabled class="text-sm text-slate-400 border border-slate-200 px-3 py-2 rounded-md cursor-not-allowed">Apply</button>
                    </div>
                    <p class="text-xs text-slate-400 -mt-2 mb-4">Coupons are coming soon.</p>

                    <dl class="space-y-2.5 text-sm border-t border-slate-100 pt-4">
                        <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-medium">{{ $money($totals['subtotal']) }}</dd></div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Shipping</dt>
                            <dd class="font-medium">{{ $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free' }}</dd>
                        </div>
                        @if ($totals['shipping'] > 0)
                            <p class="text-xs text-slate-400">Free shipping over ₹{{ number_format(\App\Services\CartService::FREE_SHIPPING_THRESHOLD, 0) }}.</p>
                        @endif
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base"><dt class="font-semibold text-brand-900">Total</dt><dd class="font-bold text-brand-900">{{ $money($totals['total']) }}</dd></div>
                    </dl>

                    <a href="{{ route('checkout.index') }}" class="mt-6 block text-center bg-brand-700 text-white py-3 rounded-full font-medium hover:bg-brand-800 transition">Proceed to Checkout</a>
                    <a href="{{ route('products.index') }}" class="mt-3 block text-center text-sm text-slate-500 hover:text-brand-700">Continue shopping</a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.public>
