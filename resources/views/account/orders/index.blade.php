<x-layouts.public title="My Orders">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-green-50 text-green-700',
            'dispatched' => 'bg-indigo-50 text-indigo-700',
            'cancelled', 'refunded' => 'bg-red-50 text-red-600',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="max-w-4xl mx-auto px-6 py-16">
        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-3xl font-bold text-brand-900">My Orders</h1>
            <a href="{{ route('wishlist.index') }}" class="text-sm text-brand-700 hover:underline">My Wishlist →</a>
        </div>

        @forelse ($orders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-4 bg-white border border-slate-100 rounded-2xl p-5 mb-4 hover:border-brand-200 transition">
                <div>
                    <p class="font-semibold text-brand-900">{{ $order->order_number }}</p>
                    <p class="text-sm text-slate-500">{{ $order->placed_at?->format('d M Y') }} · {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium capitalize {{ $badge($order->status) }}">{{ $order->status }}</span>
                    <span class="font-bold text-brand-900">{{ $money($order->total) }}</span>
                </div>
            </a>
        @empty
            <div class="text-center py-20 bg-white border border-slate-100 rounded-2xl">
                <x-icon name="shopping-bag" class="w-12 h-12 text-slate-300 mx-auto" />
                <p class="text-slate-500 mt-4">You haven't placed any orders yet.</p>
                <a href="{{ route('products.index') }}" class="inline-block mt-5 bg-brand-700 text-white px-6 py-2.5 rounded-full hover:bg-brand-800 transition">Start shopping</a>
            </div>
        @endforelse

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</x-layouts.public>
