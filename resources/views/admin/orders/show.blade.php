<x-layouts.app :title="'Order '.$order->order_number" heading="Order Details">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badge = fn ($s) => match ($s) {
            'delivered' => 'bg-success-subtle text-success',
            'dispatched' => 'bg-primary-subtle text-primary',
            'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
            default => 'bg-warning-subtle text-warning',
        };
    @endphp

    <div class="mb-4"><a href="{{ route('admin.orders.index') }}" class="fs-sm text-primary hover-underline">← All orders</a></div>

    <div class="d-grid grid-template-lg gap-6 align-items-start" style="--cols-lg: minmax(0, 1fr) 320px">
        <div class="d-flex flex-column gap-6">
            <div class="bg-body rounded-2 shadow-sm border p-6">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h2 class="fw-bold fs-lg text-body">{{ $order->order_number }}</h2>
                        <p class="fs-sm text-secondary">Placed <x-ist :value="$order->placed_at" /></p>
                    </div>
                    <span class="px-3 py-1 rounded-pill fs-sm text-capitalize {{ $badge($order->status) }}">{{ $order->status }}</span>
                </div>
                <table class="w-100 fs-sm">
                    <thead class="text-start text-secondary border-bottom">
                        <tr><th class="py-2">Item</th><th class="py-2">Seller</th><th class="py-2 text-center">Qty</th><th class="py-2 text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-bottom">
                                <td class="py-2">{{ $item->product_name }}</td>
                                <td class="py-2 text-secondary">{{ $item->seller?->business_name ?? '—' }}</td>
                                <td class="py-2 text-center">{{ $item->quantity }}</td>
                                <td class="py-2 text-end font-monospace">{{ $money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    <div class="w-48 d-flex flex-column gap-1 fs-sm">
                        <div class="d-flex justify-content-between"><span class="text-secondary">Subtotal</span><span>{{ $money($order->subtotal) }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-secondary">Shipping</span><span>{{ $order->shipping > 0 ? $money($order->shipping) : 'Free' }}</span></div>
                        <div class="d-flex justify-content-between fw-bold text-body border-top pt-1"><span>Total</span><span>{{ $money($order->total) }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Commission split for this order --}}
            <div class="bg-body rounded-2 shadow-sm border p-6">
                <h3 class="fw-semibold text-body mb-3">Commission ({{ $order->commission_credited ? 'credited' : 'pending until delivered' }})</h3>
                @if ($order->earnings->isEmpty())
                    <p class="fs-sm text-secondary">No commission on this order (no VIP seller attached).</p>
                @else
                    <table class="w-100 fs-sm">
                        <thead class="text-start text-secondary border-bottom">
                            <tr><th class="py-2">Beneficiary</th><th class="py-2">Role</th><th class="py-2 text-end">Amount</th><th class="py-2">Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($order->earnings as $earning)
                                <tr class="border-bottom">
                                    <td class="py-2">{{ $earning->beneficiary?->name ?? '—' }}</td>
                                    <td class="py-2 text-secondary">{{ ucwords(str_replace('_', ' ', $earning->role)) }}</td>
                                    <td class="py-2 text-end font-monospace">{{ $money($earning->amount) }}</td>
                                    <td class="py-2"><span class="px-2 py-1 rounded fs-xs {{ $earning->status === 'approved' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $earning->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Delivery tracking (mirrors what the customer sees) --}}
            <x-order-tracker :order="$order" />
        </div>

        {{-- Sidebar: status + delivery + customer --}}
        <div class="d-flex flex-column gap-6">
            <div class="bg-body rounded-2 shadow-sm border p-5">
                <h3 class="fw-semibold text-body mb-3">Update Status</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="d-block w-100 rounded-2 fs-sm mb-3">
                        @foreach (['pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled', 'refunded'] as $s)
                            <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-brand btn-sm w-100">Save Status</button>
                </form>
                <p class="fs-xs text-secondary mt-2">Marking an order <strong>delivered</strong> approves and credits commission to all wallets.</p>
            </div>

            <div class="bg-body rounded-2 shadow-sm border p-5">
                <h3 class="fw-semibold text-body mb-3">Expected Delivery Date</h3>
                @php $effectiveExpected = app(\App\Services\DeliveryService::class)->expectedFor($order); @endphp
                <form method="POST" action="{{ route('admin.orders.update-delivery', $order) }}">
                    @csrf @method('PATCH')
                    <input type="date" name="expected_delivery_date"
                           value="{{ old('expected_delivery_date', optional($effectiveExpected)->format('Y-m-d')) }}"
                           class="d-block w-100 rounded-2 shadow-sm fs-sm mb-3">
                    <button type="submit" class="btn btn-dark btn-sm w-100">Save Delivery Date</button>
                </form>
                <p class="fs-xs text-secondary mt-2">
                    @if ($order->expected_delivery_date)
                        Shown to the customer as the arrival estimate.
                    @else
                        Defaulting to order date + delivery window. Set a date to override.
                    @endif
                </p>
            </div>

            <div class="bg-body rounded-2 shadow-sm border p-5 fs-sm">
                <h3 class="fw-semibold text-body mb-2">Customer</h3>
                <p class="text-body-secondary">{{ $order->customer_name }}<br>{{ $order->customer_email }}<br>{{ $order->customer_phone }}</p>
                <p class="text-body-secondary mt-3">{{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                @if ($order->delivery_notes)
                    <p class="text-secondary fs-xs mt-2">Notes: {{ $order->delivery_notes }}</p>
                @endif
                @php
                    $paidVia = match (true) {
                        $order->payment_method === 'cod' => 'Cash on Delivery',
                        $order->payment_gateway === 'razorpay' => 'Online · Razorpay',
                        default => 'Online (test)',
                    };
                @endphp
                <p class="fs-xs text-secondary mt-3 text-capitalize">{{ $paidVia }} · {{ $order->payment_status }}</p>
                @if ($order->razorpay_payment_id)
                    {{-- Reference for reconciling / refunding in the Razorpay dashboard. --}}
                    <p class="fs-xs text-secondary mt-1">Payment ID: <span class="font-monospace">{{ $order->razorpay_payment_id }}</span></p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
