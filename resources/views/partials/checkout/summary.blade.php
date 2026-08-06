@php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp
<div class="bg-white border border-slate-100 rounded-2xl p-6 premium-shadow lg:sticky lg:top-24">
    <h2 class="font-semibold text-brand-900 mb-4">Your Order</h2>
    <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
        @foreach ($items as $item)
            <div class="flex items-center gap-3 text-sm">
                <span class="w-6 h-6 rounded-full bg-brand-700 text-white text-xs flex items-center justify-center flex-shrink-0">{{ $item['quantity'] }}</span>
                <span class="flex-1 min-w-0 truncate text-slate-700">{{ $item['product']->name }}</span>
                <span class="font-medium">{{ $money($item['line_total']) }}</span>
            </div>
        @endforeach
    </div>
    <dl class="space-y-2 text-sm border-t border-slate-100 pt-4">
        <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ $money($totals['subtotal']) }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Shipping</dt><dd>{{ $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free' }}</dd></div>
        <div class="flex justify-between text-base font-bold text-brand-900 border-t border-slate-100 pt-3"><dt>Total</dt><dd>{{ $money($totals['total']) }}</dd></div>
    </dl>

    @php $deliveryBy = app(\App\Services\DeliveryService::class)->promiseDate(); @endphp
    <div class="mt-4 flex items-center gap-2 text-sm bg-brand-50 border border-brand-100 rounded-lg px-3 py-2.5">
        <x-icon name="truck" class="w-4 h-4 text-brand-700 flex-shrink-0" />
        <span class="text-slate-600">Delivery by <span class="font-semibold text-brand-900">{{ $deliveryBy->format('D, d M Y') }}</span></span>
    </div>

    @if ($placeOrder ?? false)
        <button type="submit" class="mt-6 w-full bg-brand-700 text-white py-3 rounded-full font-medium hover:bg-brand-800 transition">Place Order</button>
    @else
        <p class="mt-6 text-sm text-slate-500 text-center">Sign in to place your order.</p>
    @endif

    <div class="mt-3 text-center text-sm">
        <a href="{{ route('cart.index') }}" class="text-slate-500 hover:text-brand-700">← Back to cart</a>
    </div>
</div>
