<x-layouts.app title="Orders" heading="Orders">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-success-subtle text-success',
            'dispatched' => 'bg-primary-subtle text-primary',
            'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    @endphp

    <div class="d-flex flex-wrap gap-2 mb-5">
        <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 rounded-pill fs-sm {{ ! $activeStatus ? 'bg-brand text-white' : 'bg-body border text-body-secondary' }}">All</a>
        @foreach ($statuses as $s)
            <a href="{{ route('admin.orders.index', ['status' => $s]) }}" class="px-3 py-2 rounded-pill fs-sm text-capitalize {{ $activeStatus === $s ? 'bg-brand text-white' : 'bg-body border text-body-secondary' }}">{{ $s }}</a>
        @endforeach
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Order Date</th>
                    <th class="px-4 py-3">Delivery</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Payment</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-end">Total</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3 text-nowrap text-secondary"><x-ist :value="$order->placed_at" format="d M Y, g:i A" /></td>
                        <td class="px-4 py-3 text-nowrap text-secondary">
                            @if ($order->isDelivered())
                                <span class="text-success">Delivered <x-ist :value="$order->delivered_at" format="d M" :suffix="''" /></span>
                            @elseif ($order->isCancelled())
                                <span class="text-secondary">—</span>
                            @else
                                <x-ist :value="app(\App\Services\DeliveryService::class)->expectedFor($order)" format="d M Y" :suffix="''" />
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3">
                            <span class="fs-xs {{ $order->payment_status === 'paid' ? 'text-success' : ($order->payment_status === 'failed' ? 'text-danger' : 'text-warning') }}">
                                {{ $order->payment_method === 'cod' ? 'COD' : 'Online' }} · {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded fs-xs text-capitalize {{ $badge($order->status) }}">{{ $order->status }}</span></td>
                        <td class="px-4 py-3 text-end fw-medium font-monospace">{{ $money($order->total) }}</td>
                        <td class="px-4 py-3 text-end"><a href="{{ route('admin.orders.show', $order) }}" class="text-primary hover-underline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-secondary">No orders{{ $activeStatus ? ' with this status' : ' yet' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.app>
