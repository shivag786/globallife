<x-layouts.app title="Withdrawal Requests" heading="VIP Member Withdrawals">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <p class="fs-sm text-secondary mb-5 mw-3xl">
        Withdrawal requests from VIP members against their product-commission wallet. Transfer the money
        yourself, then record the UTR number or a payment screenshot &mdash; the member sees that as proof.
        Marking a request paid is what debits their wallet.
    </p>

    <div class="d-grid grid-cols-2 grid-cols-lg-4 gap-4 mb-6">
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Pending Requests</p>
            <p class="fs-2xl fw-bold text-warning">{{ $totals['pending_count'] }}</p>
        </div>
        <div class="rounded-2 shadow-sm p-5 text-white" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))">
            <p class="fs-xs text-uppercase text-brand">Pending Amount</p>
            <p class="fs-2xl fw-bold text-gold">{{ $money($totals['pending_amount']) }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Paid Requests</p>
            <p class="fs-2xl fw-bold text-success">{{ $totals['paid_count'] }}</p>
        </div>
        <div class="bg-body rounded-2 shadow-sm p-5 border">
            <p class="fs-xs text-uppercase text-secondary">Paid Out</p>
            <p class="fs-2xl fw-bold text-brand-emphasis">{{ $money($totals['paid_amount']) }}</p>
        </div>
    </div>

    <div class="mb-4 d-flex flex-wrap align-items-center gap-2 fs-sm">
        @foreach ([null => 'All', 'pending' => 'Pending', 'paid' => 'Paid'] as $value => $label)
            <a href="{{ route('admin.withdrawals.index', $value ? ['status' => $value] : []) }}"
               class="px-3 py-2 rounded-pill border {{ $status === $value ? 'bg-brand text-white border-brand' : 'bg-body text-body-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Requested</th>
                    <th class="px-4 py-3">VIP Member</th>
                    <th class="px-4 py-3 text-end">Amount</th>
                    <th class="px-4 py-3 text-end">Wallet Now</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Payment Proof</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $wd)
                    <tr class="border-top">
                        <td class="px-4 py-3 text-secondary text-nowrap">
                            {{ $wd->created_at->format('d M Y') }}
                            <span class="d-block fs-xs text-secondary">{{ $wd->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="fw-medium text-body">{{ $wd->user->name }}</span>
                            <span class="d-block fs-xs text-secondary">{{ $wd->user->email }}</span>
                            @if ($wd->user->mobile)
                                <span class="d-block fs-xs text-secondary">{{ $wd->user->mobile }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end fw-bold font-monospace text-brand-emphasis">{{ $money($wd->amount) }}</td>
                        <td class="px-4 py-3 text-end font-monospace text-secondary">
                            {{ $money($wd->user->wallet?->balance ?? 0) }}
                            <span class="d-block fs-xs text-secondary">at request {{ $money($wd->wallet_balance_at_request) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs fw-medium text-nowrap {{ $wd->isPaid() ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                {{ $wd->isPaid() ? 'Paid' : 'Pending' }}
                            </span>
                            @if ($wd->isPaid())
                                <span class="d-block fs-xs text-secondary mt-1">
                                    {{ $wd->paid_at->format('d M Y') }} by {{ $wd->payer?->name ?? '—' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 fs-xs">
                            @if ($wd->utr_number)
                                <span class="font-monospace text-body-secondary text-break">{{ $wd->utr_number }}</span>
                            @endif
                            @if ($wd->screenshotUrl())
                                <a href="{{ $wd->screenshotUrl() }}" target="_blank" rel="noopener"
                                   class="d-block text-primary hover-underline mt-1">View screenshot</a>
                            @endif
                            @unless ($wd->hasProof())
                                <span class="text-secondary">—</span>
                            @endunless
                        </td>
                        <td class="px-4 py-3 text-end">
                            @if ($wd->isPaid())
                                <span class="fs-xs text-secondary">Settled</span>
                            @else
                                <button type="button" onclick="document.getElementById('pay-{{ $wd->id }}').showModal()"
                                        class="btn btn-success btn-sm rounded-pill fs-xs text-nowrap">
                                    Mark as Paid
                                </button>

                                {{-- The proof form. One of UTR / screenshot is required
                                     server-side too, so this cannot be bypassed. --}}
                                <dialog id="pay-{{ $wd->id }}" class="rounded-3 p-0 mw-lg text-start">
                                    <form method="POST" action="{{ route('admin.withdrawals.mark-paid', $wd) }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        @method('PATCH')

                                        <div class="px-5 py-4 border-bottom d-flex align-items-center justify-content-between">
                                            <h3 class="fw-semibold text-body">
                                                Mark {{ $money($wd->amount) }} paid to {{ $wd->user->name }}
                                            </h3>
                                            <button type="button" onclick="document.getElementById('pay-{{ $wd->id }}').close()"
                                                    class="btn btn-link p-0 text-decoration-none text-secondary" aria-label="Close">
                                                <x-icon name="x-mark" class="w-5 h-5" />
                                            </button>
                                        </div>

                                        <div class="px-5 py-4 d-flex flex-column gap-4">
                                            <p class="fs-sm text-secondary">
                                                Record at least one of the two &mdash; the member is shown this as proof.
                                                This also debits {{ $money($wd->amount) }} from their wallet.
                                            </p>

                                            <div>
                                                <label for="utr-{{ $wd->id }}" class="d-block fs-sm fw-medium text-body-secondary">UTR / Reference Number</label>
                                                <input type="text" id="utr-{{ $wd->id }}" name="utr_number" maxlength="120"
                                                       placeholder="e.g. 431298765432"
                                                       class="mt-1 d-block w-100 rounded-2 shadow-sm fs-sm">
                                            </div>

                                            <div class="text-center fs-xs text-uppercase tracking-wider text-secondary">or / and</div>

                                            <div>
                                                <label for="shot-{{ $wd->id }}" class="d-block fs-sm fw-medium text-body-secondary">Payment Screenshot</label>
                                                <input type="file" id="shot-{{ $wd->id }}" name="payment_screenshot" accept="image/*"
                                                       class="mt-1 d-block w-100 fs-sm text-body-secondary">
                                                <p class="mt-1 fs-xs text-secondary">JPG, PNG or WEBP &middot; max 4 MB.</p>
                                            </div>

                                            <div>
                                                <label for="note-{{ $wd->id }}" class="d-block fs-sm fw-medium text-body-secondary">Note (optional)</label>
                                                <input type="text" id="note-{{ $wd->id }}" name="admin_note" maxlength="500"
                                                       class="mt-1 d-block w-100 rounded-2 shadow-sm fs-sm">
                                            </div>
                                        </div>

                                        <div class="px-5 py-4 border-top d-flex align-items-center justify-content-end gap-3">
                                            <button type="button" onclick="document.getElementById('pay-{{ $wd->id }}').close()"
                                                    class="btn btn-link p-0 text-decoration-none fs-sm text-secondary px-3 py-2">Cancel</button>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                Confirm Paid
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-secondary">No withdrawal requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>

    {{-- A rejected proof must not silently close the dialog, so reopen it. --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var first = document.querySelector('dialog[id^="pay-"]');
                if (first) first.showModal();
            });
        </script>
        <div class="mt-4 rounded-2 border border-danger-subtle bg-danger-subtle px-4 py-3">
            <ul class="list-disc list-inside fs-sm text-danger">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-layouts.app>
