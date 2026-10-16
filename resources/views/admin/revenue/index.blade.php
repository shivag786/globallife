<x-layouts.app title="Revenue" heading="Company Revenue">
    <div class="d-grid grid-cols-2 grid-cols-md-5 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Activations</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">{{ $totals['count'] }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Total Package Value</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">₹{{ number_format($totals['package'], 2) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Paid to Partners</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">₹{{ number_format($totals['partners'], 2) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Paid to Managers</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">₹{{ number_format($totals['managers'], 2) }}</p>
        </div>
        <div class="rounded-2 shadow-sm p-5 text-white" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand">Company Revenue</p>
            <p class="fs-2xl fw-bold text-gold">₹{{ number_format($totals['company'], 2) }}</p>
        </div>
    </div>

    <form method="GET" class="mb-4 d-flex align-items-center gap-3">
        <label for="branch_manager_id" class="fs-sm text-body-secondary">Filter by Branch Manager</label>
        <select id="branch_manager_id" name="branch_manager_id" onchange="this.form.submit()"
                class="rounded-2 fs-sm">
            <option value="">All Branch Managers</option>
            @foreach ($branchManagers as $manager)
                <option value="{{ $manager->id }}" @selected($selectedBranchManagerId === $manager->id)>{{ $manager->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Date &amp; Time</th>
                    <th class="px-4 py-3">Branch Manager</th>
                    <th class="px-4 py-3">Commission Partner</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">Package</th>
                    <th class="px-4 py-3">Partner</th>
                    <th class="px-4 py-3">Manager</th>
                    <th class="px-4 py-3">Company</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="border-top">
                        <td class="px-4 py-3 text-secondary">{{ $transaction->activated_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3">{{ $transaction->branchManager->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $transaction->commissionPartner->name ?? '—' }}</td>
                        <td class="px-4 py-3 fw-medium">{{ $transaction->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->package_amount, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->commission_partner_amount, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->branch_manager_amount, 2) }}</td>
                        <td class="px-4 py-3 fw-semibold text-brand">₹{{ number_format($transaction->company_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-secondary">No activations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
