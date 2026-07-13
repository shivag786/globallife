<x-layouts.app title="Revenue" heading="Company Revenue">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Activations</p>
            <p class="text-2xl font-bold text-brand-900">{{ $totals['count'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Total Package Value</p>
            <p class="text-2xl font-bold text-brand-900">₹{{ number_format($totals['package'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Paid to Partners</p>
            <p class="text-2xl font-bold text-brand-900">₹{{ number_format($totals['partners'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Paid to Managers</p>
            <p class="text-2xl font-bold text-brand-900">₹{{ number_format($totals['managers'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-lg shadow-sm p-5 text-white">
            <p class="text-xs uppercase text-brand-200">Company Revenue</p>
            <p class="text-2xl font-bold text-gold-400">₹{{ number_format($totals['company'], 2) }}</p>
        </div>
    </div>

    <form method="GET" class="mb-4 flex items-center gap-3">
        <label for="branch_manager_id" class="text-sm text-slate-600">Filter by Branch Manager</label>
        <select id="branch_manager_id" name="branch_manager_id" onchange="this.form.submit()"
                class="rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Branch Managers</option>
            @foreach ($branchManagers as $manager)
                <option value="{{ $manager->id }}" @selected($selectedBranchManagerId === $manager->id)>{{ $manager->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
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
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 text-slate-500">{{ $transaction->activated_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3">{{ $transaction->branchManager->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $transaction->commissionPartner->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $transaction->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->package_amount, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->commission_partner_amount, 2) }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->branch_manager_amount, 2) }}</td>
                        <td class="px-4 py-3 font-semibold text-brand-700">₹{{ number_format($transaction->company_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-400">No activations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
