<x-layouts.app title="Business Sales" heading="Business Sales & Payouts">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <p class="fs-sm text-secondary mb-5 mw-3xl">
        Businesses selling <strong>their own products</strong> through the company checkout. Online payments land
        in the company Razorpay and are owed to the business once the order is <strong>delivered</strong>.
        Cash on Delivery is collected by the business itself, so it is shown here but never owed.
    </p>

    <div class="d-grid grid-cols-2 grid-cols-lg-4 gap-4 mb-6">
        <div class="rounded-2 shadow-sm p-5 text-white" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand-200">To transfer now</p>
            <p class="fs-2xl fw-bold text-gold-400">{{ $money($totals['payable']) }}</p>
            <p class="fs-xs text-brand-200 mb-0">{{ $totals['businesses_due'] }} business(es) waiting</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Collected online</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">{{ $money($totals['online_total']) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Cash on Delivery</p>
            <p class="fs-2xl fw-bold text-body">{{ $money($totals['cod_total']) }}</p>
            <p class="fs-xs text-secondary mb-0">collected by businesses</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Already transferred</p>
            <p class="fs-2xl fw-bold text-success">{{ $money($totals['settled']) }}</p>
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-4 py-3">Business</th>
                        <th class="px-4 py-3 text-end">Orders</th>
                        <th class="px-4 py-3 text-end">Sales</th>
                        <th class="px-4 py-3 text-end">Online paid</th>
                        <th class="px-4 py-3 text-end">COD</th>
                        <th class="px-4 py-3 text-end">Awaiting delivery</th>
                        <th class="px-4 py-3 text-end">Transferred</th>
                        <th class="px-4 py-3 text-end">To transfer</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summaries as $row)
                        @php $site = $row['microsite']; @endphp
                        <tr class="border-top">
                            <td class="px-4 py-3">
                                <span class="fw-medium">{{ $site->business_name }}</span>
                                <span class="d-block fs-xs text-secondary">
                                    {{ $site->city?->name }} · {{ $site->vipPlan?->name }}
                                    @if ($site->vipPlan && ! $site->vipPlan->sellsDirect())
                                        <span class="badge text-bg-light border">now catalogue only</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end font-monospace">
                                {{ $row['orders_count'] }}
                                @if ($row['open_count'] > 0)
                                    <span class="d-block fs-xs text-warning">{{ $row['open_count'] }} open</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($row['gross']) }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($row['online_total']) }}</td>
                            <td class="px-4 py-3 text-end font-monospace text-secondary">{{ $money($row['cod_total']) }}</td>
                            <td class="px-4 py-3 text-end font-monospace text-secondary">{{ $money($row['awaiting_delivery']) }}</td>
                            <td class="px-4 py-3 text-end font-monospace text-success">{{ $money($row['settled']) }}</td>
                            <td class="px-4 py-3 text-end font-monospace fw-bold {{ $row['payable'] > 0 ? 'text-brand-emphasis' : 'text-secondary' }}">
                                {{ $money($row['payable']) }}
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('admin.business-sales.show', $site) }}"
                                   class="btn btn-sm rounded-pill text-nowrap {{ $row['payable'] > 0 ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $row['payable'] > 0 ? 'Transfer' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-secondary">
                                No business has sold its own products online yet. Switch a package to
                                <a href="{{ route('admin.settings.selling.edit') }}" class="text-brand hover-underline">Direct sell</a>
                                to start.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
