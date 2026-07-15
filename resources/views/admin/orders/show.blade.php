<x-layouts.app :title="'Order '.$order->order_number" heading="Order Details">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-green-50 text-green-700',
            'dispatched' => 'bg-indigo-50 text-indigo-700',
            'cancelled', 'refunded' => 'bg-red-50 text-red-600',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="mb-4"><a href="{{ route('admin.orders.index') }}" class="text-sm text-indigo-600 hover:underline">← All orders</a></div>

    <div class="grid lg:grid-cols-[1fr_320px] gap-6 items-start">
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-bold text-lg text-slate-800">{{ $order->order_number }}</h2>
                        <p class="text-sm text-slate-500">{{ $order->placed_at?->format('d M Y, g:i A') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm capitalize {{ $badge($order->status) }}">{{ $order->status }}</span>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-400 border-b border-slate-100">
                        <tr><th class="py-2">Item</th><th class="py-2">Seller</th><th class="py-2 text-center">Qty</th><th class="py-2 text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b border-slate-50">
                                <td class="py-2.5">{{ $item->product_name }}</td>
                                <td class="py-2.5 text-slate-500">{{ $item->seller?->business_name ?? '—' }}</td>
                                <td class="py-2.5 text-center">{{ $item->quantity }}</td>
                                <td class="py-2.5 text-right tabular-nums">{{ $money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-end mt-3">
                    <div class="w-48 space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>{{ $money($order->subtotal) }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Shipping</span><span>{{ $order->shipping > 0 ? $money($order->shipping) : 'Free' }}</span></div>
                        <div class="flex justify-between font-bold text-slate-800 border-t border-slate-100 pt-1"><span>Total</span><span>{{ $money($order->total) }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Commission split for this order --}}
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
                <h3 class="font-semibold text-slate-800 mb-3">Commission ({{ $order->commission_credited ? 'credited' : 'pending until delivered' }})</h3>
                @if ($order->earnings->isEmpty())
                    <p class="text-sm text-slate-400">No commission on this order (no VIP seller attached).</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-left text-slate-400 border-b border-slate-100">
                            <tr><th class="py-2">Beneficiary</th><th class="py-2">Role</th><th class="py-2 text-right">Amount</th><th class="py-2">Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($order->earnings as $earning)
                                <tr class="border-b border-slate-50">
                                    <td class="py-2.5">{{ $earning->beneficiary?->name ?? '—' }}</td>
                                    <td class="py-2.5 text-slate-500">{{ ucwords(str_replace('_', ' ', $earning->role)) }}</td>
                                    <td class="py-2.5 text-right tabular-nums">{{ $money($earning->amount) }}</td>
                                    <td class="py-2.5"><span class="px-2 py-0.5 rounded text-xs {{ $earning->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ $earning->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Sidebar: status + customer --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5">
                <h3 class="font-semibold text-slate-800 mb-3">Update Status</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="block w-full rounded-md border-slate-300 shadow-sm text-sm mb-3">
                        @foreach (['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled', 'refunded'] as $s)
                            <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full bg-brand-700 text-white text-sm py-2 rounded-md hover:bg-brand-800">Save Status</button>
                </form>
                <p class="text-xs text-slate-400 mt-2">Marking an order <strong>delivered</strong> approves and credits commission to all wallets.</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 text-sm">
                <h3 class="font-semibold text-slate-800 mb-2">Customer</h3>
                <p class="text-slate-600">{{ $order->customer_name }}<br>{{ $order->customer_email }}<br>{{ $order->customer_phone }}</p>
                <p class="text-slate-600 mt-3">{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                @if ($order->delivery_notes)
                    <p class="text-slate-400 text-xs mt-2">Notes: {{ $order->delivery_notes }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-3 capitalize">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online (test)' }} · {{ $order->payment_status }}</p>
            </div>
        </div>
    </div>
</x-layouts.app>
