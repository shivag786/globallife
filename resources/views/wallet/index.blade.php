<x-layouts.app title="My Wallet" heading="My Wallet">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <p class="fs-sm text-secondary mb-6">Your product-sale commission. Earnings are pending until the order is delivered, then approved and added to your balance.</p>

    {{-- Headline balances --}}
    <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-4 mb-4">
        <div class="rounded-3 p-5 text-white" style="background: linear-gradient(135deg,#245a3f,#1b3c2c);">
            <p class="fs-xs">Withdrawable Balance</p>
            <p class="fs-2xl fw-bold mt-1">{{ $money($summary['balance']) }}</p>
        </div>
        <div class="bg-body rounded-3 border p-5">
            <p class="fs-xs text-secondary">Pending Commission</p>
            <p class="fs-2xl fw-bold text-warning mt-1">{{ $money($summary['pending']) }}</p>
        </div>
        <div class="bg-body rounded-3 border p-5">
            <p class="fs-xs text-secondary">Lifetime Earnings</p>
            <p class="fs-2xl fw-bold text-brand-emphasis mt-1">{{ $money($summary['lifetime']) }}</p>
        </div>
        <div class="bg-body rounded-3 border p-5">
            <p class="fs-xs text-secondary">Total Sales Value</p>
            <p class="fs-2xl fw-bold text-brand-emphasis mt-1">{{ $money($summary['total_sales']) }}</p>
        </div>
    </div>
    <div class="d-grid grid-cols-sm-3 gap-4 mb-8">
        <div class="bg-body rounded-3 border p-5"><p class="fs-xs text-secondary">Today's Earnings</p><p class="fs-lg fw-bold text-brand-emphasis mt-1">{{ $money($summary['today']) }}</p></div>
        <div class="bg-body rounded-3 border p-5"><p class="fs-xs text-secondary">This Month</p><p class="fs-lg fw-bold text-brand-emphasis mt-1">{{ $money($summary['monthly']) }}</p></div>
        <div class="bg-body rounded-3 border p-5"><p class="fs-xs text-secondary">Transactions</p><p class="fs-lg fw-bold text-brand-emphasis mt-1">{{ $summary['entries'] }}</p></div>
    </div>

    {{-- Withdrawals (VIP members only). The Request button stays enabled even when
         blocked, so pressing it always explains why rather than sitting dead. --}}
    @if ($isVipMember)
        <div class="bg-body rounded-3 border p-5 mb-8">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-4">
                <div>
                    <h2 class="fw-semibold text-body">Withdraw your balance</h2>
                    <p class="fs-sm text-secondary mt-1 mw-xl">
                        Minimum {{ $money($minimumWithdrawal) }} per request, and one request every 24 hours.
                        The admin transfers the money by hand and records the UTR number or a screenshot here.
                    </p>
                    <p class="fs-xs text-secondary mt-2">
                        Available to withdraw now:
                        <span class="fw-semibold text-body-secondary">{{ $money($withdrawable) }}</span>
                        @if ($withdrawable < $summary['balance'])
                            <span class="text-warning">(the rest is on an open request)</span>
                        @endif
                    </p>
                </div>

                @if ($showWithdrawForm)
                    <form method="POST" action="{{ route('withdrawals.store') }}" class="d-flex flex-wrap align-items-end gap-3">
                        @csrf
                        <div>
                            <label for="amount" class="d-block fs-xs text-secondary mb-1">Amount (₹)</label>
                            <input type="number" id="amount" name="amount" step="0.01"
                                   min="{{ $minimumWithdrawal }}"
                                   value="{{ old('amount', $defaultWithdrawAmount) }}"
                                   class="w-40 rounded-2 shadow-sm fs-sm">
                            @error('amount')<p class="fs-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-brand btn-sm">
                            Request Withdrawal
                        </button>
                    </form>
                @else
                    <p class="fs-sm text-secondary bg-body-tertiary border rounded-2 px-4 py-3">
                        You need at least {{ $money($minimumWithdrawal) }} to request a withdrawal.
                    </p>
                @endif
            </div>
        </div>

        {{-- One card per request: amount, status, and proof once it is paid. --}}
        @if ($withdrawals->isNotEmpty())
            <h2 class="fw-semibold text-body mb-3">Your withdrawal requests</h2>
            <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-4 mb-8">
                @foreach ($withdrawals as $wd)
                    <div class="bg-body rounded-3 border p-5 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <p class="fs-2xl fw-bold text-brand-emphasis">{{ $money($wd->amount) }}</p>
                                <p class="fs-xs text-secondary mt-1">Requested {{ $wd->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-pill fs-xs fw-semibold text-nowrap {{ $wd->isPaid() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                {{ $wd->isPaid() ? 'Paid' : 'Pending' }}
                            </span>
                        </div>

                        @if ($wd->isPaid())
                            <p class="fs-xs text-secondary mt-3">Paid {{ $wd->paid_at->format('d M Y') }}</p>

                            {{-- View Detail appears only once the payment succeeded. --}}
                            <button type="button" onclick="document.getElementById('wd-{{ $wd->id }}').showModal()"
                                    class="btn btn-link p-0 text-decoration-none mt-4 w-100 border border-brand-subtle text-brand fs-sm fw-semibold px-4 py-2 rounded-2">
                                View Detail
                            </button>

                            <dialog id="wd-{{ $wd->id }}" class="rounded-3 p-0 mw-md">
                                <div class="px-5 py-4 border-bottom d-flex align-items-center justify-content-between">
                                    <h3 class="fw-semibold text-body">Payment details</h3>
                                    <button type="button" onclick="document.getElementById('wd-{{ $wd->id }}').close()"
                                            class="btn btn-link p-0 text-decoration-none text-secondary" aria-label="Close">
                                        <x-icon name="x-mark" class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="px-5 py-4 d-flex flex-column gap-3 fs-sm">
                                    <div class="d-flex justify-content-between gap-4">
                                        <span class="text-secondary">Amount</span>
                                        <span class="fw-semibold text-body">{{ $money($wd->amount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between gap-4">
                                        <span class="text-secondary">Paid on</span>
                                        <span class="text-body">{{ $wd->paid_at->format('d M Y, h:i A') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between gap-4">
                                        <span class="text-secondary">UTR number</span>
                                        <span class="font-monospace text-body text-break text-end">{{ $wd->utr_number ?: '—' }}</span>
                                    </div>
                                    @if ($wd->admin_note)
                                        <div>
                                            <p class="text-secondary mb-1">Note</p>
                                            <p class="text-body-secondary">{{ $wd->admin_note }}</p>
                                        </div>
                                    @endif
                                    @if ($wd->screenshotUrl())
                                        <div>
                                            <p class="text-secondary mb-2">Payment screenshot</p>
                                            <a href="{{ $wd->screenshotUrl() }}" target="_blank" rel="noopener">
                                                <img src="{{ $wd->screenshotUrl() }}" alt="Payment screenshot"
                                                     class="w-100 rounded-2 border">
                                            </a>
                                            <p class="fs-xs text-secondary mt-1">Tap the image to open it full size.</p>
                                        </div>
                                    @endif
                                </div>
                            </dialog>
                        @else
                            <p class="fs-xs text-warning mt-3">
                                Awaiting payment from the admin. Details will appear here once it is paid.
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Transaction history --}}
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="px-5 py-3 border-bottom fw-semibold text-body">Transaction History</div>
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-start text-secondary">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3 text-end">Sale</th>
                        <th class="px-4 py-3 text-end">Rate</th>
                        <th class="px-4 py-3 text-end">Commission</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $txn)
                        <tr class="border-top">
                            <td class="px-4 py-3 text-nowrap text-secondary">{{ $txn->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 fw-medium">{{ $txn->order?->order_number ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $txn->product?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-end font-monospace">{{ $money($txn->base_amount) }}</td>
                            <td class="px-4 py-3 text-end text-secondary">{{ $txn->type === 'percent' ? rtrim(rtrim($txn->value, '0'), '.').'%' : 'fixed' }}</td>
                            <td class="px-4 py-3 text-end fw-semibold font-monospace">{{ $money($txn->amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded fs-xs {{ $txn->status === 'approved' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $txn->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-secondary">No commission yet. Earnings appear here when customers buy from your store.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
