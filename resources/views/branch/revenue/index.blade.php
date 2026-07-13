<x-layouts.app title="Revenue Tracking" heading="Revenue Tracking">
    <div class="grid sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">VIP Members Activated</p>
            <p class="text-2xl font-bold text-brand-900">{{ $totals['count'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Total Earned</p>
            <p class="text-2xl font-bold text-brand-900">₹{{ number_format($totals['earned'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">By Commission Partner</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Commission Partner</th>
                        <th class="py-2 pr-4">Activations</th>
                        <th class="py-2 pr-4">Their Earnings</th>
                        <th class="py-2">Your Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($byPartner as $row)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-4 font-medium">{{ $row->commissionPartner->name ?? '—' }}</td>
                            <td class="py-2 pr-4">{{ $row->activations }}</td>
                            <td class="py-2 pr-4">₹{{ number_format($row->partner_earned, 2) }}</td>
                            <td class="py-2 font-semibold text-brand-700">₹{{ number_format($row->your_earned, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-400">No activations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date &amp; Time</th>
                    <th class="px-4 py-3">Commission Partner</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Package Amount</th>
                    <th class="px-4 py-3">Your %</th>
                    <th class="px-4 py-3">Your Earning</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 text-slate-500">{{ $transaction->activated_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3">{{ $transaction->commissionPartner->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $transaction->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $transaction->vipMicrosite->city->name ?? '—' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($transaction->package_amount, 2) }}</td>
                        <td class="px-4 py-3">{{ $transaction->branch_manager_percentage - $transaction->commission_partner_percentage }}%</td>
                        <td class="px-4 py-3 font-semibold text-brand-700">₹{{ number_format($transaction->branch_manager_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">No activations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
