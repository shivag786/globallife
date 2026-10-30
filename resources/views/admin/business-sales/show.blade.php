<x-layouts.app :title="'Business Sales — '.$microsite->business_name" :heading="$microsite->business_name">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $statusTone = fn ($s) => match ($s) {
            'delivered' => 'bg-success-subtle text-success',
            'cancelled', 'refunded' => 'bg-danger-subtle text-danger',
            'dispatched' => 'bg-info-subtle text-info-emphasis',
            default => 'bg-warning-subtle text-warning-emphasis',
        };
    @endphp

    <a href="{{ route('admin.business-sales.index') }}" class="fs-sm text-brand hover-underline d-inline-block mb-4">← All businesses</a>

    <div class="d-grid gap-4 mb-6 grid-template-lg" style="--cols-lg: minmax(0, 1fr) 380px">
        <div class="bg-body border rounded-3 p-5">
            <p class="fs-xs text-uppercase text-secondary mb-1">Business</p>
            <p class="fw-semibold fs-lg mb-1">{{ $microsite->business_name }}</p>
            <p class="fs-sm text-secondary mb-3">
                {{ $microsite->owner_name ?: $microsite->user?->name }} · {{ $microsite->city?->name }} · {{ $microsite->vipPlan?->name }}
                ({{ $microsite->sellsDirect() ? 'Direct sell' : 'Catalogue only' }})
            </p>
            <div class="d-flex flex-wrap gap-4 fs-sm">
                @if ($microsite->phone_number) <span>📞 {{ $microsite->phone_number }}</span> @endif
                @if ($microsite->business_email) <span>✉️ {{ $microsite->business_email }}</span> @endif
                @if ($microsite->user?->email) <span class="text-secondary">Login: {{ $microsite->user->email }}</span> @endif
            </div>
            <p class="fs-xs text-secondary mt-3 mb-0">
                Bank details are not stored in the app — confirm the account with the business before transferring.
            </p>
        </div>

        <div class="rounded-3 p-5 text-white d-flex flex-column" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand-200 mb-1">To transfer now</p>
            <p class="fs-3xl fw-bold text-gold-400 mb-1">{{ $money($payable) }}</p>
            <p class="fs-xs text-brand-200">{{ $payableCount }} delivered order(s) paid online, not yet transferred.</p>
            @if ($payable > 0)
                <button type="button" onclick="document.getElementById('settle-dialog').showModal()"
                        class="btn btn-warning fw-semibold mt-auto">
                    Record transfer
                </button>
            @endif
        </div>
    </div>

    @if ($payable > 0)
        <dialog id="settle-dialog" class="rounded-3 p-0 mw-lg w-100 text-start border-0 shadow-lg">
            <form method="POST" action="{{ route('admin.business-sales.settle', $microsite) }}" enctype="multipart/form-data"
                  data-confirm="Record a transfer of {{ $money($payable) }} to {{ $microsite->business_name }}? These {{ $payableCount }} orders will be marked as paid out."
                  data-confirm-button="Yes, record it">
                @csrf
                <div class="px-5 py-4 border-bottom d-flex align-items-center justify-content-between">
                    <h3 class="fw-semibold mb-0">Transfer {{ $money($payable) }}</h3>
                    <button type="button" onclick="document.getElementById('settle-dialog').close()" class="btn btn-link p-0 text-secondary" aria-label="Close">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
                <div class="px-5 py-4 d-flex flex-column gap-4">
                    <p class="fs-sm text-secondary mb-0">
                        Send the money to the business first (NEFT / IMPS / UPI), then record it here. The amount is
                        fixed to what is owed so it always matches the orders it covers. The business sees this proof
                        under My Orders.
                    </p>
                    <div>
                        <label for="utr_number" class="form-label fs-sm fw-medium">UTR / Transaction number</label>
                        <input type="text" id="utr_number" name="utr_number" maxlength="120" class="form-control" placeholder="e.g. 431298765432" value="{{ old('utr_number') }}">
                    </div>
                    <div class="text-center fs-xs text-uppercase tracking-wider text-secondary">or / and</div>
                    <div>
                        <label for="payment_screenshot" class="form-label fs-sm fw-medium">Screenshot of the transfer</label>
                        <input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/*" class="form-control">
                        <p class="fs-xs text-secondary mt-1 mb-0">JPG, PNG or WEBP · max 4 MB.</p>
                    </div>
                    <div>
                        <label for="note" class="form-label fs-sm fw-medium">Note (optional)</label>
                        <input type="text" id="note" name="note" maxlength="500" class="form-control" value="{{ old('note') }}">
                    </div>
                </div>
                <div class="px-5 py-4 border-top d-flex justify-content-end gap-3">
                    <button type="button" onclick="document.getElementById('settle-dialog').close()" class="btn btn-light">Cancel</button>
                    <button type="submit" class="btn btn-success">Save transfer</button>
                </div>
            </form>
        </dialog>
    @endif

    <h2 class="fs-lg fw-semibold mb-3">Transfers made</h2>
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-end">Amount</th>
                        <th class="px-4 py-3 text-end">Orders</th>
                        <th class="px-4 py-3">UTR / Proof</th>
                        <th class="px-4 py-3">By</th>
                        <th class="px-4 py-3">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settlements as $settlement)
                        <tr class="border-top">
                            <td class="px-4 py-3 text-nowrap"><x-ist :value="$settlement->paid_at" format="d M Y, h:i A" /></td>
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
                            <td class="px-4 py-3">{{ $settlement->payer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-secondary">{{ $settlement->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No transfers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h2 class="fs-lg fw-semibold mb-0">Orders</h2>
        <div class="d-flex gap-2 fs-sm">
            @foreach ([null => 'All', 'payable' => 'To transfer', 'cod' => 'Cash on Delivery'] as $value => $label)
                <a href="{{ route('admin.business-sales.show', [$microsite] + ($value ? ['filter' => $value] : [])) }}"
                   class="px-3 py-2 rounded-pill border {{ $filter === $value ? 'bg-brand text-white border-brand' : 'bg-body text-body-secondary' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
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
                        <th class="px-4 py-3">Payout</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-top align-top">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="fw-medium text-brand hover-underline font-monospace">{{ $order->order_number }}</a>
                                <span class="d-block fs-xs text-secondary"><x-ist :value="$order->placed_at" format="d M Y" :suffix="''" /></span>
                            </td>
                            <td class="px-4 py-3">
                                {{ $order->customer_name }}
                                <span class="d-block fs-xs text-secondary">{{ $order->city }}</span>
                            </td>
                            <td class="px-4 py-3 fs-xs text-secondary">
                                @foreach ($order->items as $item)
                                    <span class="d-block">{{ $item->quantity }} × {{ $item->product_name }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-uppercase fs-xs fw-semibold">{{ $order->payment_method === 'cod' ? 'COD' : 'Online' }}</span>
                                <span class="d-block fs-xs text-secondary">{{ ucfirst($order->payment_status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded fs-xs fw-medium {{ $statusTone($order->status) }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-end fw-semibold font-monospace">{{ $money($order->total) }}</td>
                            <td class="px-4 py-3 fs-xs">
                                @if ($order->payment_method === 'cod')
                                    <span class="text-secondary">Collected by business</span>
                                @elseif ($order->settlement)
                                    <span class="text-success">Transferred <x-ist :value="$order->settlement->paid_at" format="d M" :suffix="''" /></span>
                                @elseif ($order->isDelivered() && $order->payment_status === 'paid')
                                    <span class="text-brand-emphasis fw-semibold">Due</span>
                                @elseif ($order->isCancelled())
                                    <span class="text-danger">{{ $order->payment_status === 'paid' ? 'Refund to customer' : '—' }}</span>
                                @else
                                    <span class="text-secondary">After delivery</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.app>
