<x-layouts.public :title="'Order '.$order->order_number">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-success-subtle text-success',
            'dispatched' => 'bg-primary-subtle text-primary',
            'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    @endphp

    <div class="mw-3xl mx-auto px-6 py-16">
        <a href="{{ route('account.orders.index') }}" class="fs-sm text-brand hover-underline">← All orders</a>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 mb-6">
            <div>
                <h1 class="font-display fs-2xl fw-bold text-brand-emphasis">{{ $order->order_number }}</h1>
                <p class="fs-sm text-secondary">Placed <x-ist :value="$order->placed_at" /></p>
            </div>
            <span class="px-3 py-1 rounded-pill fs-sm fw-medium text-capitalize {{ $badge($order->status) }}">{{ $order->status }}</span>
        </div>

        {{-- Delivery tracking --}}
        <div class="mb-6">
            <x-order-tracker :order="$order" />
        </div>

        <div class="bg-body border rounded-4 divide-y">
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

        <div class="d-grid grid-cols-sm-2 gap-4 mt-6">
            <div class="bg-body border rounded-4 p-5 fs-sm">
                <p class="fw-medium text-brand-emphasis mb-1">Delivery address</p>
                <p class="text-body-secondary">{{ $order->customer_name }}<br>{{ $order->customer_phone }}<br>{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
            </div>
            <div class="bg-body border rounded-4 p-5 fs-sm">
                <p class="fw-medium text-brand-emphasis mb-1">Payment</p>
                <p class="text-body-secondary text-capitalize">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online (test)' }}</p>
                <p class="text-secondary fs-xs mt-1 text-capitalize">Payment status: {{ $order->payment_status }}</p>
                @if ($order->delivery_notes)
                    <p class="mt-3 fw-medium text-brand-emphasis">Notes</p>
                    <p class="text-body-secondary">{{ $order->delivery_notes }}</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>
