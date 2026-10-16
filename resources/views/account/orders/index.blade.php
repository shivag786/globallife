<x-layouts.public title="My Orders">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-success-subtle text-success',
            'dispatched' => 'bg-primary-subtle text-primary',
            'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    @endphp

    <div class="mw-4xl mx-auto px-6 py-16">
        <div class="d-flex align-items-center justify-content-between mb-8">
            <h1 class="font-display fs-3xl fw-bold text-brand-emphasis">My Orders</h1>
            <div class="d-flex align-items-center gap-4 fs-sm">
                <a href="{{ route('account.addresses.index') }}" class="text-brand hover-underline">My Addresses</a>
                <a href="{{ route('wishlist.index') }}" class="text-brand hover-underline">My Wishlist →</a>
            </div>
        </div>

        @php $delivery = app(\App\Services\DeliveryService::class); @endphp
        @forelse ($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="d-block bg-body border rounded-4 p-5 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                    <div>
                        <p class="fw-semibold text-brand-emphasis">{{ $order->order_number }}</p>
                        <p class="fs-sm text-secondary">
                            <x-ist :value="$order->placed_at" format="d M Y" :suffix="''" /> · {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <span class="px-2 py-1 rounded-pill fs-xs fw-medium text-capitalize {{ $badge($order->status) }}">{{ $order->status }}</span>
                        <span class="fw-bold text-brand-emphasis">{{ $money($order->total) }}</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2 fs-sm">
                    @if ($order->isDelivered())
                        <x-icon name="check-circle" class="w-4 h-4 text-success" />
                        <span class="text-success">Delivered on <x-ist :value="$order->delivered_at" format="d M Y" :suffix="''" /></span>
                    @elseif ($order->isCancelled())
                        <x-icon name="x-mark" class="w-4 h-4 text-danger" />
                        <span class="text-danger text-capitalize">{{ $order->status }}</span>
                    @else
                        <x-icon name="truck" class="w-4 h-4 text-brand" />
                        <span class="text-body-secondary">Arriving by <span class="fw-medium text-brand-emphasis">{{ optional($delivery->expectedFor($order))->timezone('Asia/Kolkata')->format('D, d M') }}</span></span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-20 bg-body border rounded-4">
                <x-icon name="shopping-bag" class="w-12 h-12 text-body-tertiary mx-auto" />
                <p class="text-secondary mt-4">You haven't placed any orders yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-brand rounded-pill d-inline-block mt-5">Start shopping</a>
            </div>
        @endforelse

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</x-layouts.public>
