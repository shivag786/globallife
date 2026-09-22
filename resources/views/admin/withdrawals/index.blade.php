<x-layouts.app title="Withdrawal Requests" heading="VIP Member Withdrawals">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 2); @endphp

    <p class="text-sm text-slate-500 mb-5 max-w-3xl">
        Withdrawal requests from VIP members against their product-commission wallet. Transfer the money
        yourself, then record the UTR number or a payment screenshot &mdash; the member sees that as proof.
        Marking a request paid is what debits their wallet.
    </p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Pending Requests</p>
            <p class="text-2xl font-bold text-amber-600">{{ $totals['pending_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-lg shadow-sm p-5 text-white">
            <p class="text-xs uppercase text-brand-200">Pending Amount</p>
            <p class="text-2xl font-bold text-gold-400">{{ $money($totals['pending_amount']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Paid Requests</p>
            <p class="text-2xl font-bold text-green-700">{{ $totals['paid_count'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <p class="text-xs uppercase text-slate-400">Paid Out</p>
            <p class="text-2xl font-bold text-brand-900">{{ $money($totals['paid_amount']) }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        @foreach ([null => 'All', 'pending' => 'Pending', 'paid' => 'Paid'] as $value => $label)
            <a href="{{ route('admin.withdrawals.index', $value ? ['status' => $value] : []) }}"
               class="px-3 py-1.5 rounded-full border {{ $status === $value ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Requested</th>
                    <th class="px-4 py-3">VIP Member</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Wallet Now</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Payment Proof</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $wd)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                            {{ $wd->created_at->format('d M Y') }}
                            <span class="block text-xs text-slate-400">{{ $wd->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $wd->user->name }}</span>
                            <span class="block text-xs text-slate-400">{{ $wd->user->email }}</span>
                            @if ($wd->user->mobile)
                                <span class="block text-xs text-slate-400">{{ $wd->user->mobile }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold tabular-nums text-brand-900">{{ $money($wd->amount) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-500">
                            {{ $money($wd->user->wallet?->balance ?? 0) }}
                            <span class="block text-xs text-slate-400">at request {{ $money($wd->wallet_balance_at_request) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap {{ $wd->isPaid() ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $wd->isPaid() ? 'Paid' : 'Pending' }}
                            </span>
                            @if ($wd->isPaid())
                                <span class="block text-xs text-slate-400 mt-0.5">
                                    {{ $wd->paid_at->format('d M Y') }} by {{ $wd->payer?->name ?? '—' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if ($wd->utr_number)
                                <span class="font-mono text-slate-700 break-all">{{ $wd->utr_number }}</span>
                            @endif
                            @if ($wd->screenshotUrl())
                                <a href="{{ $wd->screenshotUrl() }}" target="_blank" rel="noopener"
                                   class="block text-indigo-600 hover:underline mt-0.5">View screenshot</a>
                            @endif
                            @unless ($wd->hasProof())
                                <span class="text-slate-400">—</span>
                            @endunless
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($wd->isPaid())
                                <span class="text-xs text-slate-400">Settled</span>
                            @else
                                <button type="button" onclick="document.getElementById('pay-{{ $wd->id }}').showModal()"
                                        class="bg-green-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-green-700 whitespace-nowrap">
                                    Mark as Paid
                                </button>

                                {{-- The proof form. One of UTR / screenshot is required
                                     server-side too, so this cannot be bypassed. --}}
                                <dialog id="pay-{{ $wd->id }}" class="rounded-xl p-0 w-[92vw] max-w-lg backdrop:bg-slate-900/50 text-left">
                                    <form method="POST" action="{{ route('admin.withdrawals.mark-paid', $wd) }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        @method('PATCH')

                                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                                            <h3 class="font-semibold text-slate-800">
                                                Mark {{ $money($wd->amount) }} paid to {{ $wd->user->name }}
                                            </h3>
                                            <button type="button" onclick="document.getElementById('pay-{{ $wd->id }}').close()"
                                                    class="text-slate-400 hover:text-slate-700" aria-label="Close">
                                                <x-icon name="x-mark" class="w-5 h-5" />
                                            </button>
                                        </div>

                                        <div class="px-5 py-4 space-y-4">
                                            <p class="text-sm text-slate-500">
                                                Record at least one of the two &mdash; the member is shown this as proof.
                                                This also debits {{ $money($wd->amount) }} from their wallet.
                                            </p>

                                            <div>
                                                <label for="utr-{{ $wd->id }}" class="block text-sm font-medium text-slate-700">UTR / Reference Number</label>
                                                <input type="text" id="utr-{{ $wd->id }}" name="utr_number" maxlength="120"
                                                       placeholder="e.g. 431298765432"
                                                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                            </div>

                                            <div class="text-center text-xs uppercase tracking-wider text-slate-400">or / and</div>

                                            <div>
                                                <label for="shot-{{ $wd->id }}" class="block text-sm font-medium text-slate-700">Payment Screenshot</label>
                                                <input type="file" id="shot-{{ $wd->id }}" name="payment_screenshot" accept="image/*"
                                                       class="mt-1 block w-full text-sm text-slate-600">
                                                <p class="mt-1 text-xs text-slate-500">JPG, PNG or WEBP &middot; max 4 MB.</p>
                                            </div>

                                            <div>
                                                <label for="note-{{ $wd->id }}" class="block text-sm font-medium text-slate-700">Note (optional)</label>
                                                <input type="text" id="note-{{ $wd->id }}" name="admin_note" maxlength="500"
                                                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                                            </div>
                                        </div>

                                        <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                                            <button type="button" onclick="document.getElementById('pay-{{ $wd->id }}').close()"
                                                    class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Cancel</button>
                                            <button type="submit" class="bg-green-600 text-white text-sm font-semibold px-5 py-2.5 rounded-md hover:bg-green-700">
                                                Confirm Paid
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No withdrawal requests yet.</td></tr>
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
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-layouts.app>
