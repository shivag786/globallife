<x-layouts.shop title="Order Confirmed" :storefront="$storefront">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <div class="mw-2xl mx-auto px-6 py-16">
        <div class="text-center">
            <div class="w-16 h-16 rounded-pill bg-success-subtle d-flex align-items-center justify-content-center mx-auto">
                <x-icon name="check-circle" class="w-9 h-9 text-success" />
            </div>
            <h1 class="font-display fs-3xl fw-bold text-brand-emphasis mt-5">Thank you for your order!</h1>
            <p class="text-secondary mt-2">Order <span class="fw-semibold text-brand-emphasis">{{ $order->order_number }}</span> is confirmed.</p>
            @php $arrivingBy = app(\App\Services\DeliveryService::class)->expectedFor($order); @endphp
            @if ($arrivingBy)
                <div class="mt-4 d-inline-flex align-items-center gap-2 bg-brand-subtle border border-brand-subtle rounded-pill px-4 py-2">
                    <x-icon name="truck" class="w-5 h-5 text-brand" />
                    <span class="fs-sm text-body-secondary">Arriving by <span class="fw-semibold text-brand-emphasis">{{ $arrivingBy->timezone('Asia/Kolkata')->format('D, d M Y') }}</span></span>
                </div>
            @endif
        </div>

        @if ($newAccount)
            <div class="mt-8 bg-brand-subtle border border-brand-subtle rounded-4 p-5 text-center">
                <p class="fw-semibold text-brand-emphasis">We've created your account</p>
                <p class="fs-sm text-body-secondary mt-1">Your login details have been emailed to <strong>{{ $order->customer_email }}</strong>. You're already signed in.</p>
            </div>
        @endif

        <div class="mt-8 bg-body border rounded-4 divide-y">
            @foreach ($order->items as $item)
                <div class="d-flex align-items-center gap-4 p-4">
                    <div class="flex-grow-1 min-w-0">
                        <p class="fw-medium text-brand-emphasis">{{ $item->product_name }}</p>
                        <p class="fs-sm text-secondary">{{ $item->quantity }} × {{ $money($item->unit_price) }}</p>
                    </div>
                    <div class="fw-semibold text-brand-emphasis">{{ $money($item->line_total) }}</div>
                </div>
            @endforeach
            <div class="p-4 d-flex flex-column gap-2 fs-sm">
                <div class="d-flex justify-content-between"><span class="text-secondary">Subtotal</span><span>{{ $money($order->subtotal) }}</span></div>
                <div class="d-flex justify-content-between"><span class="text-secondary">Shipping</span><span>{{ $order->shipping > 0 ? $money($order->shipping) : 'Free' }}</span></div>
                <div class="d-flex justify-content-between fs-base fw-bold text-brand-emphasis border-top pt-2"><span>Total</span><span>{{ $money($order->total) }}</span></div>
            </div>
        </div>

        <div class="mt-6 bg-body border rounded-4 p-5 fs-sm text-body-secondary">
            <p class="fw-medium text-brand-emphasis mb-1">Delivery to</p>
            <p>{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
            <p>{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
            <p class="mt-2 fs-xs text-secondary">Payment: {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid online (test)' }}</p>
        </div>

        <div class="mt-8 d-flex flex-wrap justify-content-center gap-3">
            @auth
                <a href="{{ route('account.orders.index') }}" class="btn btn-brand rounded-pill">View my orders</a>
            @endauth
            <a href="{{ $continueUrl }}" class="border border-brand text-brand px-6 py-2 rounded-pill d-inline-flex align-items-center gap-2">
                <x-icon name="shopping-bag" class="w-4 h-4" />
                {{ $storefront ? 'Continue shopping at '.\Illuminate\Support\Str::limit($storefront->business_name, 24) : 'Continue shopping' }}
            </a>
        </div>
    </div>
</x-layouts.shop>
