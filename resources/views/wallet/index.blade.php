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

    {{-- Withdrawals (VIP members only). The Request button stays enabled even when
         blocked, so pressing it always explains why rather than sitting dead. --}}
    @if ($isVipMember)
        <div class="bg-white rounded-xl border border-slate-100 p-5 mb-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-800">Withdraw your balance</h2>
                    <p class="text-sm text-slate-500 mt-1 max-w-xl">
                        Minimum {{ $money($minimumWithdrawal) }} per request, and one request every 24 hours.
                        The admin transfers the money by hand and records the UTR number or a screenshot here.
                    </p>
                    <p class="text-xs text-slate-400 mt-2">
                        Available to withdraw now:
                        <span class="font-semibold text-slate-600">{{ $money($withdrawable) }}</span>
                        @if ($withdrawable < $summary['balance'])
                            <span class="text-amber-600">(the rest is on an open request)</span>
                        @endif
                    </p>
                </div>

                @if ($showWithdrawForm)
                    <form method="POST" action="{{ route('withdrawals.store') }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label for="amount" class="block text-xs text-slate-500 mb-1">Amount (₹)</label>
                            <input type="number" id="amount" name="amount" step="0.01"
                                   min="{{ $minimumWithdrawal }}"
                                   value="{{ old('amount', $defaultWithdrawAmount) }}"
                                   class="w-40 rounded-md border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-md hover:bg-brand-800">
                            Request Withdrawal
                        </button>
                    </form>
                @else
                    <p class="text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded-md px-4 py-3">
                        You need at least {{ $money($minimumWithdrawal) }} to request a withdrawal.
                    </p>
                @endif
            </div>
        </div>

        {{-- One card per request: amount, status, and proof once it is paid. --}}
        @if ($withdrawals->isNotEmpty())
            <h2 class="font-semibold text-slate-800 mb-3">Your withdrawal requests</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach ($withdrawals as $wd)
                    <div class="bg-white rounded-xl border border-slate-100 p-5 flex flex-col">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-2xl font-bold text-brand-900">{{ $money($wd->amount) }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">Requested {{ $wd->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $wd->isPaid() ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $wd->isPaid() ? 'Paid' : 'Pending' }}
                            </span>
                        </div>

                        @if ($wd->isPaid())
                            <p class="text-xs text-slate-400 mt-3">Paid {{ $wd->paid_at->format('d M Y') }}</p>

                            {{-- View Detail appears only once the payment succeeded. --}}
                            <button type="button" onclick="document.getElementById('wd-{{ $wd->id }}').showModal()"
                                    class="mt-4 w-full border border-brand-200 text-brand-700 text-sm font-semibold px-4 py-2 rounded-md hover:bg-brand-50">
                                View Detail
                            </button>

                            <dialog id="wd-{{ $wd->id }}" class="rounded-xl p-0 w-[92vw] max-w-md backdrop:bg-slate-900/50">
                                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                    <h3 class="font-semibold text-slate-800">Payment details</h3>
                                    <button type="button" onclick="document.getElementById('wd-{{ $wd->id }}').close()"
                                            class="text-slate-400 hover:text-slate-700" aria-label="Close">
                                        <x-icon name="x-mark" class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="px-5 py-4 space-y-3 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Amount</span>
                                        <span class="font-semibold text-slate-800">{{ $money($wd->amount) }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">Paid on</span>
                                        <span class="text-slate-800">{{ $wd->paid_at->format('d M Y, h:i A') }}</span>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-500">UTR number</span>
                                        <span class="font-mono text-slate-800 break-all text-right">{{ $wd->utr_number ?: '—' }}</span>
                                    </div>
                                    @if ($wd->admin_note)
                                        <div>
                                            <p class="text-slate-500 mb-1">Note</p>
                                            <p class="text-slate-700">{{ $wd->admin_note }}</p>
                                        </div>
                                    @endif
                                    @if ($wd->screenshotUrl())
                                        <div>
                                            <p class="text-slate-500 mb-1.5">Payment screenshot</p>
                                            <a href="{{ $wd->screenshotUrl() }}" target="_blank" rel="noopener">
                                                <img src="{{ $wd->screenshotUrl() }}" alt="Payment screenshot"
                                                     class="w-full rounded-lg border border-slate-200">
                                            </a>
                                            <p class="text-xs text-slate-400 mt-1">Tap the image to open it full size.</p>
                                        </div>
                                    @endif
                                </div>
                            </dialog>
                        @else
                            <p class="text-xs text-amber-600 mt-3">
                                Awaiting payment from the admin. Details will appear here once it is paid.
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif

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
