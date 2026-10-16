<x-layouts.app title="Revenue Tracking" heading="Revenue Tracking">
    <div class="d-grid grid-cols-sm-2 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">VIP Members Activated</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">{{ $totals['count'] }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Total Earned</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">₹{{ number_format($totals['earned'], 2) }}</p>
        </div>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Date &amp; Time</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Package Amount</th>
                    <th class="px-4 py-3">Your %</th>
                    <th class="px-4 py-3">Your Earning</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="border-top">
                        <td class="px-4 py-3 text-secondary">{{ $transaction->activated_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3 fw-medium">{{ $transaction->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $transaction->vipMicrosite->city->name ?? '—' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->package_amount, 2) }}</td>
                        <td class="px-4 py-3">{{ $transaction->commission_partner_percentage }}%</td>
                        <td class="px-4 py-3 fw-semibold text-brand">₹{{ number_format($transaction->commission_partner_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No activations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
