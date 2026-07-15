<x-layouts.app title="My Wallet" heading="My Wallet">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <p class="text-sm text-slate-500 mb-6">Your product-sale commission. Earnings are pending until the order is delivered, then approved and added to your balance.</p>

    {{-- Headline balances --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="rounded-xl p-5 text-white" style="background: linear-gradient(135deg,#245a3f,#1b3c2c);">
            <p class="text-xs text-brand-100/80">Withdrawable Balance</p>
            <p class="text-2xl font-bold mt-1">{{ $money($summary['balance']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <p class="text-xs text-slate-400">Pending Commission</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $money($summary['pending']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <p class="text-xs text-slate-400">Lifetime Earnings</p>
            <p class="text-2xl font-bold text-brand-900 mt-1">{{ $money($summary['lifetime']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <p class="text-xs text-slate-400">Total Sales Value</p>
            <p class="text-2xl font-bold text-brand-900 mt-1">{{ $money($summary['total_sales']) }}</p>
        </div>
    </div>
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-100 p-5"><p class="text-xs text-slate-400">Today's Earnings</p><p class="text-lg font-bold text-brand-900 mt-1">{{ $money($summary['today']) }}</p></div>
        <div class="bg-white rounded-xl border border-slate-100 p-5"><p class="text-xs text-slate-400">This Month</p><p class="text-lg font-bold text-brand-900 mt-1">{{ $money($summary['monthly']) }}</p></div>
        <div class="bg-white rounded-xl border border-slate-100 p-5"><p class="text-xs text-slate-400">Transactions</p><p class="text-lg font-bold text-brand-900 mt-1">{{ $summary['entries'] }}</p></div>
    </div>

    {{-- Transaction history --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">Transaction History</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3 text-right">Sale</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-right">Commission</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $txn)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $txn->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $txn->order?->order_number ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $txn->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $money($txn->base_amount) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ $txn->type === 'percent' ? rtrim(rtrim($txn->value, '0'), '.').'%' : 'fixed' }}</td>
                            <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ $money($txn->amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs {{ $txn->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ $txn->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No commission yet. Earnings appear here when customers buy from your store.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
