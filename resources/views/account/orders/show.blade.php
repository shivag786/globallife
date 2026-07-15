<x-layouts.public :title="'Order '.$order->order_number">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-green-50 text-green-700',
            'dispatched' => 'bg-indigo-50 text-indigo-700',
            'cancelled', 'refunded' => 'bg-red-50 text-red-600',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="max-w-3xl mx-auto px-6 py-16">
        <a href="{{ route('account.orders.index') }}" class="text-sm text-brand-700 hover:underline">← All orders</a>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-4 mb-8">
            <div>
                <h1 class="font-display text-2xl font-bold text-brand-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-slate-500">Placed {{ $order->placed_at?->format('d M Y, g:i A') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-medium capitalize {{ $badge($order->status) }}">{{ $order->status }}</span>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl divide-y divide-slate-100">
            @foreach ($order->items as $item)
                <div class="flex items-center gap-4 p-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-brand-900">{{ $item->product_name }}</p>
                        <p class="text-sm text-slate-500">{{ $item->quantity }} × {{ $money($item->unit_price) }}</p>
                    </div>
                    <div class="font-semibold text-brand-900">{{ $money($item->line_total) }}</div>
                </div>
            @endforeach
            <div class="p-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>{{ $money($order->subtotal) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Shipping</span><span>{{ $order->shipping > 0 ? $money($order->shipping) : 'Free' }}</span></div>
                <div class="flex justify-between text-base font-bold text-brand-900 border-t border-slate-100 pt-2"><span>Total</span><span>{{ $money($order->total) }}</span></div>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 mt-6">
            <div class="bg-white border border-slate-100 rounded-2xl p-5 text-sm">
                <p class="font-medium text-brand-900 mb-1">Delivery address</p>
                <p class="text-slate-600">{{ $order->customer_name }}<br>{{ $order->customer_phone }}<br>{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
            </div>
            <div class="bg-white border border-slate-100 rounded-2xl p-5 text-sm">
                <p class="font-medium text-brand-900 mb-1">Payment</p>
                <p class="text-slate-600 capitalize">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Online (test)' }}</p>
                <p class="text-slate-400 text-xs mt-1 capitalize">Payment status: {{ $order->payment_status }}</p>
                @if ($order->delivery_notes)
                    <p class="mt-3 font-medium text-brand-900">Notes</p>
                    <p class="text-slate-600">{{ $order->delivery_notes }}</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>
