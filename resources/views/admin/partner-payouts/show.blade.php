<x-layouts.app :title="$partner->name.' — Payout'" :heading="$partner->name">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $walletBalance = (float) ($partner->wallet?->balance ?? 0);
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="text-sm text-slate-500">
            Commission statement for <strong>{{ $label }}</strong> ·
            Branch Manager: {{ $partner->creator?->name ?? '—' }} · {{ $partner->email }}
        </p>
        <a href="{{ route('admin.partner-payouts.index', ['period' => $period]) }}"
           class="text-sm text-indigo-600 hover:underline">&larr; All partners</a>
    </div>

    <form method="GET" class="mb-5 flex flex-wrap items-center gap-3">
        <label for="period" class="text-sm text-slate-600">Month</label>
        <select id="period" name="period" onchange="this.form.submit()"
                class="rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($periods as $value => $optionLabel)
                <option value="{{ $value }}" @selected($value === $period)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="px-3 py-1.5 text-sm rounded bg-slate-800 text-white">Show</button></noscript>
    </form>

    {{-- Month totals --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Product Commission</p>
            <p class="text-xl font-bold text-brand-900">{{ $money($totals['product_approved']) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $totals['entries'] }} entries</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">VIP Plan Commission</p>
            <p class="text-xl font-bold text-brand-900">{{ $money($totals['vip_amount']) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $totals['activations'] }} activations</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Total Earned</p>
            <p class="text-xl font-bold text-brand-900">{{ $money($totals['earned']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Pending</p>
            <p class="text-xl font-bold text-amber-600">{{ $money($totals['product_pending']) }}</p>
            <p class="text-xs text-slate-400 mt-1">until delivered</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Already Paid</p>
            <p class="text-xl font-bold text-green-700">{{ $money($totals['paid']) }}</p>
        </div>
        <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-lg shadow-sm p-4 text-white">
            <p class="text-xs uppercase text-brand-200">Payable Now</p>
            <p class="text-xl font-bold text-gold-400">{{ $money($totals['payable']) }}</p>
        </div>
    </div>

    {{-- Settle the month --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-slate-800">Settle {{ $label }}</h2>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">
                    Marking paid clears the withdrawable amount for this month
                    ({{ $money($totals['product_payable']) }} product + {{ $money($totals['vip_payable']) }} VIP plan)
                    and takes the product share off the partner's wallet. Pending commission
                    ({{ $money($totals['product_pending']) }}) is left as it is and becomes payable here once those
                    orders are delivered.
                </p>
                <p class="text-xs text-slate-400 mt-2">
                    Wallet balance right now: <span class="font-semibold text-slate-600">{{ $money($walletBalance) }}</span>
                </p>
            </div>

            @if ($totals['payable'] > 0)
                <form method="POST" action="{{ route('admin.partner-payouts.mark-paid', $partner) }}"
                      class="flex flex-wrap items-end gap-3"
                      data-confirm="Mark {{ $money($totals['payable']) }} as paid to {{ $partner->name }} for {{ $label }}?">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <div>
                        <label for="reference" class="block text-xs text-slate-500 mb-1">Reference (optional)</label>
                        <input type="text" id="reference" name="reference" maxlength="120" value="{{ old('reference') }}"
                               placeholder="UTR / cheque no."
                               class="rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="note" class="block text-xs text-slate-500 mb-1">Note (optional)</label>
                        <input type="text" id="note" name="note" maxlength="500" value="{{ old('note') }}"
                               class="rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                        Mark {{ $money($totals['payable']) }} paid
                    </button>
                </form>
            @else
                <span class="px-3 py-2 rounded-md bg-slate-100 text-slate-500 text-sm">
                    Nothing payable for this month.
                </span>
            @endif
        </div>

        @error('period')<p class="text-sm text-red-600 mt-3">{{ $message }}</p>@enderror
        @error('reference')<p class="text-sm text-red-600 mt-3">{{ $message }}</p>@enderror
        @error('note')<p class="text-sm text-red-600 mt-3">{{ $message }}</p>@enderror
    </div>

    {{-- Product-sale commission --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">Product Sale Commission</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Seller</th>
                        <th class="px-4 py-3 text-right">Sale</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-right">Commission</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($earnings as $earning)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $earning->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">
                                @if ($earning->order)
                                    <a href="{{ route('admin.orders.show', $earning->order) }}" class="text-indigo-600 hover:underline">{{ $earning->order->order_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $earning->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $earning->sellerMicrosite?->business_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $money($earning->base_amount) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">
                                {{ $earning->type === 'percent' ? rtrim(rtrim($earning->value, '0'), '.').'%' : 'fixed' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $money($earning->amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs {{ $earning->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $earning->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-slate-400">No product commission in {{ $label }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- VIP-activation commission --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">VIP Plan (Joining) Commission</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Activated</th>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3">City</th>
                        <th class="px-4 py-3">Plan</th>
                        <th class="px-4 py-3 text-right">Package</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activations as $txn)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $txn->activated_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $txn->vipMicrosite?->business_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $txn->vipMicrosite?->city?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $txn->vipPlan?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $money($txn->package_amount) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ rtrim(rtrim($txn->commission_partner_percentage, '0'), '.') }}%</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $money($txn->commission_partner_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No VIP activations in {{ $label }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- What has already been settled --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">Payout History for {{ $label }}</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Paid On</th>
                        <th class="px-4 py-3">By</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Note</th>
                        <th class="px-4 py-3 text-right">Product</th>
                        <th class="px-4 py-3 text-right">VIP</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payouts as $payout)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $payout->paid_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3">{{ $payout->payer?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $payout->reference ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $payout->note ?: '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $money($payout->product_amount) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $money($payout->vip_amount) }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $money($payout->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">Not paid yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
