<x-layouts.app title="Partner Payouts" heading="Commission Partner Payouts">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badges = [
            'unpaid' => ['Unpaid', 'bg-red-50 text-red-600'],
            'part_paid' => ['Part paid', 'bg-amber-50 text-amber-700'],
            'paid' => ['Paid', 'bg-green-50 text-green-700'],
            'paid_pending_remains' => ['Paid so far', 'bg-green-50 text-green-700'],
            'awaiting_delivery' => ['Awaiting delivery', 'bg-slate-100 text-slate-500'],
            'no_activity' => ['No activity', 'bg-slate-100 text-slate-400'],
        ];
    @endphp

    <p class="text-sm text-slate-500 mb-4">
        What every Commission Partner earned in {{ $periodLabel }} — product-sale commission plus VIP plan
        (joining) commission. Product commission stays <strong>pending</strong> until the order is delivered;
        only approved amounts are payable.
    </p>

    <form method="GET" class="mb-5 flex flex-wrap items-center gap-3">
        <label for="period" class="text-sm text-slate-600">Month</label>
        <select id="period" name="period" onchange="this.form.submit()"
                class="rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($periods as $value => $label)
                <option value="{{ $value }}" @selected($value === $period)>{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="px-3 py-1.5 text-sm rounded bg-slate-800 text-white">Show</button></noscript>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Earned (approved)</p>
            <p class="text-2xl font-bold text-brand-900">{{ $money($summary['earned']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Pending</p>
            <p class="text-2xl font-bold text-amber-600">{{ $money($summary['pending']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Already Paid</p>
            <p class="text-2xl font-bold text-green-700">{{ $money($summary['paid']) }}</p>
        </div>
        <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-lg shadow-sm p-5 text-white">
            <p class="text-xs uppercase text-brand-200">Payable Now</p>
            <p class="text-2xl font-bold text-gold-400">{{ $money($summary['payable']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Commission Partner</th>
                    <th class="px-4 py-3">Branch Manager</th>
                    <th class="px-4 py-3 text-right">Product</th>
                    <th class="px-4 py-3 text-right">VIP Plans</th>
                    <th class="px-4 py-3 text-right">Earned</th>
                    <th class="px-4 py-3 text-right">Pending</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Payable</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php [$badgeLabel, $badgeClass] = $badges[$row['status']]; @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $row['partner']->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $row['partner']->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $money($row['product_approved']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $money($row['vip_amount']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold">{{ $money($row['earned']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-amber-600">{{ $money($row['product_pending']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-green-700">{{ $money($row['paid']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums font-bold">{{ $money($row['payable']) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs whitespace-nowrap {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.partner-payouts.show', ['partner' => $row['partner'], 'period' => $period]) }}"
                               class="text-indigo-600 hover:underline whitespace-nowrap">View details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-6 text-center text-slate-400">No commission partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
