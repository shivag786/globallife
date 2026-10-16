@php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp
<div class="bg-body border rounded-4 p-6 premium-shadow position-sticky top-24">
    <h2 class="fw-semibold text-brand-emphasis mb-4">Your Order</h2>
    <div class="d-flex flex-column gap-3 mh-64 overflow-y-auto mb-4">
        @foreach ($items as $item)
            <div class="d-flex align-items-center gap-3 fs-sm">
                <span class="w-6 h-6 rounded-pill bg-brand text-white fs-xs d-flex align-items-center justify-content-center flex-shrink-0">{{ $item['quantity'] }}</span>
                <span class="flex-grow-1 min-w-0 text-truncate text-body-secondary">{{ $item['product']->name }}</span>
                <span class="fw-medium">{{ $money($item['line_total']) }}</span>
            </div>
        @endforeach
    </div>
    <dl class="d-flex flex-column gap-2 fs-sm border-top pt-4">
        <div class="d-flex justify-content-between"><dt class="text-secondary">Subtotal</dt><dd>{{ $money($totals['subtotal']) }}</dd></div>
        <div class="d-flex justify-content-between"><dt class="text-secondary">Shipping</dt><dd>{{ $totals['shipping'] > 0 ? $money($totals['shipping']) : 'Free' }}</dd></div>
        <div class="d-flex justify-content-between fs-base fw-bold text-brand-emphasis border-top pt-3"><dt>Total</dt><dd>{{ $money($totals['total']) }}</dd></div>
    </dl>

    @php $deliveryBy = app(\App\Services\DeliveryService::class)->promiseDate(); @endphp
    <div class="mt-4 d-flex align-items-center gap-2 fs-sm bg-brand-subtle border border-brand-subtle rounded-2 px-3 py-2">
        <x-icon name="truck" class="w-4 h-4 text-brand flex-shrink-0" />
        <span class="text-body-secondary">Delivery by <span class="fw-semibold text-brand-emphasis">{{ $deliveryBy->format('D, d M Y') }}</span></span>
    </div>

    @if ($placeOrder ?? false)
        <button type="submit" class="btn btn-brand rounded-pill mt-6 w-100">Place Order</button>
    @else
        <p class="mt-6 fs-sm text-secondary text-center">Sign in to place your order.</p>
    @endif

    <div class="mt-3 text-center fs-sm">
        <a href="{{ route('cart.index') }}" class="text-secondary">← Back to cart</a>
    </div>
</div>
