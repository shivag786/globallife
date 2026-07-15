<x-layouts.public title="Order Confirmed">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <div class="max-w-2xl mx-auto px-6 py-16">
        <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                <x-icon name="check-circle" class="w-9 h-9 text-green-600" />
            </div>
            <h1 class="font-display text-3xl font-bold text-brand-900 mt-5">Thank you for your order!</h1>
            <p class="text-slate-500 mt-2">Order <span class="font-semibold text-brand-800">{{ $order->order_number }}</span> is confirmed.</p>
        </div>

        @if ($newAccount)
            <div class="mt-8 bg-brand-50 border border-brand-100 rounded-2xl p-5 text-center">
                <p class="font-semibold text-brand-900">We've created your account</p>
                <p class="text-sm text-slate-600 mt-1">Your login details have been emailed to <strong>{{ $order->customer_email }}</strong>. You're already signed in.</p>
            </div>
        @endif

        <div class="mt-8 bg-white border border-slate-100 rounded-2xl divide-y divide-slate-100">
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

        <div class="mt-6 bg-white border border-slate-100 rounded-2xl p-5 text-sm text-slate-600">
            <p class="font-medium text-brand-900 mb-1">Delivery to</p>
            <p>{{ $order->customer_name }} · {{ $order->customer_phone }}</p>
            <p>{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
            <p class="mt-2 text-xs text-slate-400">Payment: {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Paid online (test)' }}</p>
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ route('account.orders.index') }}" class="bg-brand-700 text-white px-6 py-2.5 rounded-full hover:bg-brand-800 transition">View my orders</a>
            @endauth
            <a href="{{ route('products.index') }}" class="border border-brand-600 text-brand-700 px-6 py-2.5 rounded-full hover:bg-brand-50 transition">Continue shopping</a>
        </div>
    </div>
</x-layouts.public>
