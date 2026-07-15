<x-layouts.app title="Orders" heading="Orders">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-green-50 text-green-700',
            'dispatched' => 'bg-indigo-50 text-indigo-700',
            'cancelled', 'refunded' => 'bg-red-50 text-red-600',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('admin.orders.index') }}" class="px-3 py-1.5 rounded-full text-sm {{ ! $activeStatus ? 'bg-brand-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-300' }}">All</a>
        @foreach ($statuses as $s)
            <a href="{{ route('admin.orders.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-full text-sm capitalize {{ $activeStatus === $s ? 'bg-brand-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-300' }}">{{ $s }}</a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Payment</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $order->placed_at?->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs {{ $order->payment_status === 'paid' ? 'text-green-600' : ($order->payment_status === 'failed' ? 'text-red-600' : 'text-amber-600') }}">
                                {{ $order->payment_method === 'cod' ? 'COD' : 'Online' }} · {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs capitalize {{ $badge($order->status) }}">{{ $order->status }}</span></td>
                        <td class="px-4 py-3 text-right font-medium tabular-nums">{{ $money($order->total) }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:underline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No orders{{ $activeStatus ? ' with this status' : ' yet' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.app>
