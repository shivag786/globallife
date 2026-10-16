<x-layouts.app title="Partner Payouts" heading="Commission Partner Payouts">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
        $badges = [
            'unpaid' => ['Unpaid', 'bg-danger-subtle text-danger'],
            'part_paid' => ['Part paid', 'bg-warning-subtle text-warning'],
            'paid' => ['Paid', 'bg-success-subtle text-success'],
            'paid_pending_remains' => ['Paid so far', 'bg-success-subtle text-success'],
            'awaiting_delivery' => ['Awaiting delivery', 'bg-body-secondary text-secondary'],
            'no_activity' => ['No activity', 'bg-body-secondary text-secondary'],
        ];
    @endphp

    <p class="fs-sm text-secondary mb-4">
        What every Commission Partner earned in {{ $periodLabel }} — product-sale commission plus VIP plan
        (joining) commission. Product commission stays <strong>pending</strong> until the order is delivered;
        only approved amounts are payable.
    </p>

    <form method="GET" class="mb-5 d-flex flex-wrap align-items-center gap-3">
        <label for="period" class="fs-sm text-body-secondary">Month</label>
        <select id="period" name="period" onchange="this.form.submit()"
                class="rounded-2 fs-sm">
            @foreach ($periods as $value => $label)
                <option value="{{ $value }}" @selected($value === $period)>{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="btn btn-dark btn-sm">Show</button></noscript>
    </form>

    <div class="d-grid grid-cols-2 grid-cols-md-4 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Earned (approved)</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">{{ $money($summary['earned']) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Pending</p>
            <p class="fs-2xl fw-bold text-warning">{{ $money($summary['pending']) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Already Paid</p>
            <p class="fs-2xl fw-bold text-success">{{ $money($summary['paid']) }}</p>
        </div>
        <div class="rounded-2 shadow-sm p-5 text-white" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand">Payable Now</p>
            <p class="fs-2xl fw-bold text-gold">{{ $money($summary['payable']) }}</p>
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Commission Partner</th>
                    <th class="px-4 py-3">Branch Manager</th>
                    <th class="px-4 py-3 text-end">Product</th>
                    <th class="px-4 py-3 text-end">VIP Plans</th>
                    <th class="px-4 py-3 text-end">Earned</th>
                    <th class="px-4 py-3 text-end">Pending</th>
                    <th class="px-4 py-3 text-end">Paid</th>
                    <th class="px-4 py-3 text-end">Payable</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php [$badgeLabel, $badgeClass] = $badges[$row['status']]; @endphp
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $row['partner']->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $row['partner']->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-end font-monospace">{{ $money($row['product_approved']) }}</td>
                        <td class="px-4 py-3 text-end font-monospace">{{ $money($row['vip_amount']) }}</td>
                        <td class="px-4 py-3 text-end font-monospace fw-semibold">{{ $money($row['earned']) }}</td>
                        <td class="px-4 py-3 text-end font-monospace text-warning">{{ $money($row['product_pending']) }}</td>
                        <td class="px-4 py-3 text-end font-monospace text-success">{{ $money($row['paid']) }}</td>
                        <td class="px-4 py-3 text-end font-monospace fw-bold">{{ $money($row['payable']) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs text-nowrap {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('admin.partner-payouts.show', ['partner' => $row['partner'], 'period' => $period]) }}"
                               class="text-primary hover-underline text-nowrap">View details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-6 text-center text-secondary">No commission partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
