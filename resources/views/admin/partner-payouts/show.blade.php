<x-layouts.app :title="$partner->name.' — Payout'" :heading="$partner->name">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $walletBalance = (float) ($partner->wallet?->balance ?? 0);
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <p class="fs-sm text-secondary">
            Commission statement for <strong>{{ $label }}</strong> ·
            Branch Manager: {{ $partner->creator?->name ?? '—' }} · {{ $partner->email }}
        </p>
        <a href="{{ route('admin.partner-payouts.index', ['period' => $period]) }}"
           class="fs-sm text-primary hover-underline">&larr; All partners</a>
    </div>

    <form method="GET" class="mb-5 d-flex flex-wrap align-items-center gap-3">
        <label for="period" class="fs-sm text-body-secondary">Month</label>
        <select id="period" name="period" onchange="this.form.submit()"
                class="rounded-2 fs-sm">
            @foreach ($periods as $value => $optionLabel)
                <option value="{{ $value }}" @selected($value === $period)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="btn btn-dark btn-sm">Show</button></noscript>
    </form>

    {{-- Month totals --}}
    <div class="d-grid grid-cols-2 grid-cols-md-3 grid-cols-lg-6 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-4 border">
            <p class="fs-xs text-uppercase text-secondary">Product Commission</p>
            <p class="fs-xl fw-bold text-brand-emphasis">{{ $money($totals['product_approved']) }}</p>
            <p class="fs-xs text-secondary mt-1">{{ $totals['entries'] }} entries</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-4 border">
            <p class="fs-xs text-uppercase text-secondary">VIP Plan Commission</p>
            <p class="fs-xl fw-bold text-brand-emphasis">{{ $money($totals['vip_amount']) }}</p>
            <p class="fs-xs text-secondary mt-1">{{ $totals['activations'] }} activations</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-4 border">
            <p class="fs-xs text-uppercase text-secondary">Total Earned</p>
            <p class="fs-xl fw-bold text-brand-emphasis">{{ $money($totals['earned']) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-4 border">
            <p class="fs-xs text-uppercase text-secondary">Pending</p>
            <p class="fs-xl fw-bold text-warning">{{ $money($totals['product_pending']) }}</p>
            <p class="fs-xs text-secondary mt-1">until delivered</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-4 border">
            <p class="fs-xs text-uppercase text-secondary">Already Paid</p>
            <p class="fs-xl fw-bold text-success">{{ $money($totals['paid']) }}</p>
        </div>
        <div class="rounded-2 shadow-sm p-4 text-white" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand-">Payable Now</p>
            <p class="fs-xl fw-bold text-gold-400">{{ $money($totals['payable']) }}</p>
        </div>
    </div>

    {{-- Settle the month --}}
    <div class="bg-body rounded-2 shadow-sm border p-5 mb-8">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-4">
            <div>
                <h2 class="fw-semibold text-body">Settle {{ $label }}</h2>
                <p class="fs-sm text-secondary mt-1 mw-xl">
                    Marking paid clears the withdrawable amount for this month
                    ({{ $money($totals['product_payable']) }} product + {{ $money($totals['vip_payable']) }} VIP plan)
                    and takes the product share off the partner's wallet. Pending commission
                    ({{ $money($totals['product_pending']) }}) is left as it is and becomes payable here once those
                    orders are delivered.
                </p>
                <p class="fs-xs text-secondary mt-2">
                    Wallet balance right now: <span class="fw-semibold text-body-secondary">{{ $money($walletBalance) }}</span>
                </p>
            </div>

            @if ($totals['payable'] > 0)
                <form method="POST" action="{{ route('admin.partner-payouts.mark-paid', $partner) }}"
                      class="d-flex flex-wrap align-items-end gap-3"
                      data-confirm="Mark {{ $money($totals['payable']) }} as paid to {{ $partner->name }} for {{ $label }}?">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <div>
                        <label for="reference" class="d-block fs-xs text-secondary mb-1">Reference (optional)</label>
                        <input type="text" id="reference" name="reference" maxlength="120" value="{{ old('reference') }}"
                               placeholder="UTR / cheque no."
                               class="rounded-2 fs-sm">
                    </div>
                    <div>
                        <label for="note" class="d-block fs-xs text-secondary mb-1">Note (optional)</label>
                        <input type="text" id="note" name="note" maxlength="500" value="{{ old('note') }}"
                               class="rounded-2 fs-sm">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        Mark {{ $money($totals['payable']) }} paid
                    </button>
                </form>
            @else
                <span class="px-3 py-2 rounded-2 bg-body-secondary text-secondary fs-sm">
                    Nothing payable for this month.
                </span>
            @endif
        </div>

        @error('period')<p class="fs-sm text-danger mt-3">{{ $message }}</p>@enderror
        @error('reference')<p class="fs-sm text-danger mt-3">{{ $message }}</p>@enderror
        @error('note')<p class="fs-sm text-danger mt-3">{{ $message }}</p>@enderror
    </div>

    {{-- Product-sale commission --}}
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden mb-8">
        <div class="px-5 py-3 border-bottom fw-semibold text-body">Product Sale Commission</div>
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-start text-secondary">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Seller</th>
                        <th class="px-4 py-3 text-end">Sale</th>
                        <th class="px-4 py-3 text-end">Rate</th>
                        <th class="px-4 py-3 text-end">Commission</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($earnings as $earning)
                        <tr class="border-top">
                            <td class="px-4 py-3 text-secondary text-nowrap">{{ $earning->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 fw-medium">
                                @if ($earning->order)
                                    <a href="{{ route('admin.orders.show', $earning->order) }}" class="text-primary hover-underline">{{ $earning->order->order_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $earning->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-secondary">{{ $earning->sellerMicrosite?->business_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($earning->base_amount) }}</td>
                            <td class="px-4 py-3 text-end text-secondary">
                                {{ $earning->type === 'percent' ? rtrim(rtrim($earning->value, '0'), '.').'%' : 'fixed' }}
                            </td>
                            <td class="px-4 py-3 text-end fw-semibold font-monospace">{{ $money($earning->amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded fs-xs {{ $earning->status === 'approved' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                    {{ $earning->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-secondary">No product commission in {{ $label }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- VIP-activation commission --}}
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden mb-8">
        <div class="px-5 py-3 border-bottom fw-semibold text-body">VIP Plan (Joining) Commission</div>
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-start text-secondary">
                    <tr>
                        <th class="px-4 py-3">Activated</th>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">City</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3 text-end">Package</th>
                        <th class="px-4 py-3 text-end">Rate</th>
                        <th class="px-4 py-3 text-end">Commission</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activations as $txn)
                        <tr class="border-top">
                            <td class="px-4 py-3 text-secondary text-nowrap">{{ $txn->activated_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $txn->vipMicrosite?->business_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-secondary">{{ $txn->vipMicrosite?->city?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-secondary">{{ $txn->vipPlan?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($txn->package_amount) }}</td>
                            <td class="px-4 py-3 text-end text-secondary">{{ rtrim(rtrim($txn->commission_partner_percentage, '0'), '.') }}%</td>
                            <td class="px-4 py-3 text-end fw-semibold font-monospace">{{ $money($txn->commission_partner_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No VIP activations in {{ $label }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- What has already been settled --}}
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="px-5 py-3 border-bottom fw-semibold text-body">Payout History for {{ $label }}</div>
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-start text-secondary">
                    <tr>
                        <th class="px-4 py-3">Paid On</th>
                        <th class="px-4 py-3">By</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Note</th>
                        <th class="px-4 py-3 text-end">Product</th>
                        <th class="px-4 py-3 text-end">VIP</th>
                        <th class="px-4 py-3 text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payouts as $payout)
                        <tr class="border-top">
                            <td class="px-4 py-3 text-secondary text-nowrap">{{ $payout->paid_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3">{{ $payout->payer?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $payout->reference ?: '—' }}</td>
                            <td class="px-4 py-3 text-secondary">{{ $payout->note ?: '—' }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($payout->product_amount) }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($payout->vip_amount) }}</td>
                            <td class="px-4 py-3 text-end fw-semibold font-monospace">{{ $money($payout->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">Not paid yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
