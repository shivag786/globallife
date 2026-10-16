<x-layouts.app title="Revenue Tracking" heading="Revenue Tracking">
    <div class="d-grid grid-cols-sm-3 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">VIP Members Activated</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">{{ $totals['count'] }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">You Earned</p>
            <p class="fs-2xl fw-bold text-brand">₹{{ number_format($totals['earned'], 2) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Partners Earned</p>
            <p class="fs-2xl fw-bold text-body">₹{{ number_format($totals['partners_earned'], 2) }}</p>
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
        <h2 class="fw-semibold mb-3">By Commission Partner</h2>
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="text-start text-secondary">
                    <tr>
                        <th class="py-2 pe-4">Commission Partner</th>
                        <th class="py-2 pe-4">Activations</th>
                        <th class="py-2 pe-4">Their Earnings</th>
                        <th class="py-2">Your Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byPartner as $row)
                        <tr class="border-top">
                            <td class="py-2 pe-4 fw-medium">{{ $row->commissionPartner->name ?? '—' }}</td>
                            <td class="py-2 pe-4">{{ $row->activations }}</td>
                            <td class="py-2 pe-4">₹{{ number_format($row->partner_earned, 2) }}</td>
                            <td class="py-2 fw-semibold text-brand">₹{{ number_format($row->your_earned, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-secondary">No activations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Date &amp; Time</th>
                    <th class="px-4 py-3">Commission Partner</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Package Amount</th>
                    <th class="px-4 py-3">Partner Earned</th>
                    <th class="px-4 py-3">You Earned</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="border-top">
                        <td class="px-4 py-3 text-secondary">{{ $transaction->activated_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3">{{ $transaction->commissionPartner->name ?? '—' }}</td>
                        <td class="px-4 py-3 fw-medium">{{ $transaction->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $transaction->vipMicrosite->city->name ?? '—' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->package_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            ₹{{ number_format($transaction->commission_partner_amount, 2) }}
                            <span class="fs-xs text-secondary">({{ rtrim(rtrim(number_format($transaction->commission_partner_percentage, 2), '0'), '.') }}%)</span>
                        </td>
                        <td class="px-4 py-3 fw-semibold text-brand">
                            ₹{{ number_format($transaction->branch_manager_amount, 2) }}
                            <span class="fs-xs text-secondary">({{ rtrim(rtrim(number_format($transaction->branch_manager_percentage - $transaction->commission_partner_percentage, 2), '0'), '.') }}%)</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No activations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
