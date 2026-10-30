<x-layouts.app title="My Orders" heading="My Orders">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $statusTone = fn ($s) => match ($s) {
            'delivered' => 'bg-success-subtle text-success',
            'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
            'dispatched' => 'bg-info-subtle text-info-emphasis',
            default => 'bg-warning-subtle text-warning-emphasis',
        };
    @endphp

    <p class="fs-sm text-secondary mb-5 mw-3xl">
        Orders customers placed for <strong>your own products</strong> on your page. You pack and deliver them,
        and update the status here so the customer can track it. Online payments are collected by the company and
        transferred to you after delivery; Cash on Delivery you collect yourself.
    </p>

    @unless ($sellsDirect)
        <div class="alert alert-info fs-sm">
            Your package shows your products as a <strong>catalogue</strong> — customers order them on WhatsApp, so new
            orders will not appear here. Ask your Commission Partner about a package with online ordering.
        </div>
    @endunless

    <div class="d-grid grid-cols-2 grid-cols-lg-3 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">To deliver</p>
            <p class="fs-2xl fw-bold {{ $openCount ? 'text-warning' : 'text-body' }}">{{ $openCount }}</p>
        </div>
        <div class="rounded-2 shadow-sm p-5 text-white" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand-200">Due to you from the company</p>
            <p class="fs-2xl fw-bold text-gold-400">{{ $money($payable) }}</p>
            <p class="fs-xs text-brand-200 mb-0">delivered online orders, not yet transferred</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Received so far</p>
            <p class="fs-2xl fw-bold text-success">{{ $money($settlements->sum('amount')) }}</p>
        </div>
    </div>

    <div class="mb-4 d-flex flex-wrap gap-2 fs-sm">
        @foreach ([null => 'All', 'open' => 'To deliver', 'delivered' => 'Delivered'] as $value => $label)
            <a href="{{ route('vip.orders.index', $value ? ['status' => $value] : []) }}"
               class="px-3 py-2 rounded-pill border {{ $filter === $value ? 'bg-brand text-white border-brand' : 'bg-body text-body-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-top align-top">
                            <td class="px-4 py-3">
                                <span class="fw-medium font-monospace">{{ $order->order_number }}</span>
                                <span class="d-block fs-xs text-secondary"><x-ist :value="$order->placed_at" format="d M Y, g:i A" /></span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $order->customer_name }}
                                <span class="d-block fs-xs text-secondary">{{ $order->customer_phone }} · {{ $order->city }}</span>
                            </td>
                            <td class="px-4 py-3 fs-xs text-secondary">
                                @foreach ($order->items as $item)
                                    <span class="d-block">{{ $item->quantity }} × {{ $item->product_name }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3">
                                @if ($order->payment_method === 'cod')
                                    <span class="badge text-bg-warning">Collect cash {{ $money($order->total) }}</span>
                                @else
                                    <span class="badge text-bg-success">Paid online</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded fs-xs fw-medium {{ $statusTone($order->status) }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-end fw-semibold font-monospace">{{ $money($order->total) }}</td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('vip.orders.show', $order) }}" class="btn btn-sm btn-outline-brand rounded-pill text-nowrap">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-secondary">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mb-8">{{ $orders->links() }}</div>

    <h2 class="fs-lg fw-semibold mb-3">Money received from the company</h2>
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-end">Amount</th>
                        <th class="px-4 py-3 text-end">Orders</th>
                        <th class="px-4 py-3">UTR / Proof</th>
                        <th class="px-4 py-3">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settlements as $settlement)
                        <tr class="border-top">
                            <td class="px-4 py-3"><x-ist :value="$settlement->paid_at" format="d M Y" :suffix="''" /></td>
                            <td class="px-4 py-3 text-end fw-bold font-monospace">{{ $money($settlement->amount) }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $settlement->orders_count }}</td>
                            <td class="px-4 py-3 fs-xs">
                                @if ($settlement->utr_number)
                                    <span class="font-monospace">{{ $settlement->utr_number }}</span>
                                @endif
                                @if ($settlement->payment_screenshot_path)
                                    <a href="{{ asset('storage/'.$settlement->payment_screenshot_path) }}" target="_blank" rel="noopener" class="d-block text-primary hover-underline">View screenshot</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-secondary">{{ $settlement->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">Nothing transferred yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
