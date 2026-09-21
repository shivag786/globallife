<x-layouts.app :title="'Renew '.$member->name" :heading="'Renew Membership'">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 0);
        $current = $microsite->vipPlan;
        $daysLeft = $microsite->daysUntilExpiry() ?? 0;
        $expired = $microsite->hasExpiredPlan();
    @endphp

    {{-- Who and why, with the lapse stated plainly up top. --}}
    <div class="rounded-2xl overflow-hidden premium-shadow mb-6" style="background: linear-gradient(135deg,#1b3c2c,#0d2118);">
        <div class="px-6 py-5 sm:px-8 sm:py-6 flex flex-wrap items-start justify-between gap-5">
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-widest text-gold-400 font-semibold">Membership Renewal</p>
                <h2 class="font-display text-2xl sm:text-3xl font-bold text-white mt-1 truncate">{{ $member->name }}</h2>
                <p class="text-sm text-brand-200 mt-1 truncate">
                    {{ $microsite->business_name }}
                    @if ($microsite->city) &middot; {{ $microsite->city->name }} @endif
                    &middot; {{ $member->email }}
                </p>
            </div>

            {{-- Two states: still live but due, or already lapsed and offline. --}}
            <div class="flex items-center gap-2 rounded-xl px-4 py-3 border
                        {{ $expired ? 'bg-red-500/15 border-red-400/30' : 'bg-gold-500/15 border-gold-400/30' }}">
                <x-icon name="{{ $expired ? 'x-mark' : 'calendar' }}"
                        class="w-5 h-5 flex-shrink-0 {{ $expired ? 'text-red-300' : 'text-gold-400' }}" />
                <div>
                    @if ($expired)
                        <p class="text-sm font-semibold text-red-200">
                            Expired {{ $microsite->plan_expires_at->format('d M Y') }}
                        </p>
                        <p class="text-xs text-red-300/80">
                            {{ abs($daysLeft) }} day{{ abs($daysLeft) === 1 ? '' : 's' }} ago &middot; page showing maintenance notice
                        </p>
                    @else
                        <p class="text-sm font-semibold text-gold-400">
                            Expires {{ $microsite->plan_expires_at->format('d M Y') }}
                        </p>
                        <p class="text-xs text-brand-200">
                            in {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} &middot; page still live
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Usage strip: the numbers that decide which package actually fits. --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 border-t border-white/10 divide-y sm:divide-y-0 sm:divide-x divide-white/10">
            <div class="px-6 py-4">
                <p class="text-[11px] uppercase tracking-wider text-brand-300">Previous package</p>
                <p class="text-white font-semibold mt-0.5 truncate">{{ $current->name ?? '—' }}</p>
            </div>
            <div class="px-6 py-4">
                <p class="text-[11px] uppercase tracking-wider text-brand-300">Products in use</p>
                <p class="text-white font-semibold mt-0.5">
                    {{ $productQuota['used'] }}
                    <span class="text-brand-300 font-normal text-sm">/ {{ $productQuota['limit'] }}</span>
                </p>
            </div>
            <div class="px-6 py-4">
                <p class="text-[11px] uppercase tracking-wider text-brand-300">Services in use</p>
                <p class="text-white font-semibold mt-0.5">
                    {{ $serviceQuota['used'] }}
                    <span class="text-brand-300 font-normal text-sm">/ {{ $serviceQuota['limit'] }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
        <div>
            <h3 class="font-display text-xl font-bold text-brand-900">Choose the package they paid for</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-2xl">
                Payment is collected offline &mdash; approving simply records it.
                The package sets how long the page stays live and the total products and services they may
                have; anything already added counts towards it.
                @if (! $expired)
                    Renewing now adds the new term on top of the {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }}
                    still remaining, so nothing is lost by renewing early.
                @else
                    The new term runs from today.
                @endif
            </p>
        </div>
        <a href="{{ route('manager.vip-members.index') }}" class="text-sm text-brand-700 hover:underline whitespace-nowrap">
            &larr; Back to VIP Members
        </a>
    </div>

    {{-- Each card is its own form, so the confirmation dialog can name the exact
         package, price, validity and caps being approved. --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10 items-stretch">
        @foreach ($packages as $package)
            <x-package-card :package="$package"
                            :products-used="$productQuota['used']"
                            :services-used="$serviceQuota['used']"
                            :current-label="$current && $current->id === $package->id ? 'their previous package' : null">
                <form method="POST" action="{{ route('manager.vip-members.renewal.approve', $member) }}"
                      data-confirm="Confirm {{ $money($package->renewal_price) }} received for the {{ $package->name }} package? {{ $member->name }}'s page is live until {{ $microsite->plan_expires_at->isFuture() ? $microsite->plan_expires_at->copy()->addMonths($package->validityMonths())->format('d M Y') : now()->addMonths($package->validityMonths())->format('d M Y') }}, with {{ $package->productLimit() }} products and {{ $package->serviceLimit() }} services allowed."
                      data-confirm-title="Approve {{ $package->name }} renewal"
                      data-confirm-button="Yes, approve">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="vip_plan_id" value="{{ $package->id }}">
                    <button type="submit"
                            class="w-full py-3 rounded-full font-semibold text-sm transition
                                   {{ $package->isMostPopular()
                                        ? 'bg-gold-500 text-brand-950 hover:bg-gold-400'
                                        : 'bg-brand-700 text-white hover:bg-brand-800' }}">
                        Approve &amp; Renew
                    </button>
                </form>
            </x-package-card>
        @endforeach
    </div>

    {{-- At-a-glance comparison, the same shape as the printed package sheet. --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-10">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-display text-lg font-bold text-brand-900">Compare packages</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-brand-900 text-white text-left">
                        <th class="px-5 py-3 font-semibold">Feature</th>
                        @foreach ($packages as $package)
                            <th class="px-5 py-3 font-semibold text-center whitespace-nowrap">
                                {{ strtoupper($package->name) }}
                                <span class="block font-normal text-gold-400 text-xs mt-0.5">{{ $money($package->renewal_price) }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left font-semibold text-slate-700 bg-slate-50">Validity</th>
                        @foreach ($packages as $package)
                            <td class="px-5 py-3 text-center text-slate-600">{{ $package->validityLabel() }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-left font-semibold text-slate-700 bg-slate-50">Product catalogue</th>
                        @foreach ($packages as $package)
                            <td class="px-5 py-3 text-center font-semibold {{ $productQuota['used'] <= $package->productLimit() ? 'text-brand-800' : 'text-red-600' }}">
                                {{ $package->productLimit() }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-700 bg-slate-50">Services</th>
                        @foreach ($packages as $package)
                            <td class="px-5 py-3 text-center font-semibold {{ $serviceQuota['used'] <= $package->serviceLimit() ? 'text-brand-800' : 'text-red-600' }}">
                                {{ $package->serviceLimit() }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="px-5 py-3 text-xs text-slate-400 border-t border-slate-100">
            Figures in red sit below this member's current usage of
            {{ $productQuota['used'] }} product{{ $productQuota['used'] === 1 ? '' : 's' }} and
            {{ $serviceQuota['used'] }} service{{ $serviceQuota['used'] === 1 ? '' : 's' }}.
        </p>
    </div>

    {{-- Reject, kept visually quiet and well away from the approve buttons. --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-slate-800">Not renewing?</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">
                    Records the refusal and leaves the page on the maintenance notice. Their content, leads and
                    reviews are all kept, and you can approve a package here whenever they pay.
                </p>
            </div>
            <form method="POST" action="{{ route('manager.vip-members.renewal.reject', $member) }}"
                  data-confirm="Reject {{ $member->name }}'s renewal? Their page stays on the maintenance notice until you approve a package."
                  data-confirm-title="Reject renewal" data-confirm-button="Yes, reject" data-confirm-danger>
                @csrf
                @method('PATCH')
                <button type="submit" class="border border-red-300 text-red-600 text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-red-50 transition whitespace-nowrap">
                    Reject renewal
                </button>
            </form>
        </div>
    </div>

    @if ($history->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-display text-lg font-bold text-brand-900">Renewal history</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Date</th>
                            <th class="px-5 py-3 font-medium">Decision</th>
                            <th class="px-5 py-3 font-medium">Package</th>
                            <th class="px-5 py-3 font-medium text-right">Amount</th>
                            <th class="px-5 py-3 font-medium">Valid until</th>
                            <th class="px-5 py-3 font-medium">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr class="border-t border-slate-100">
                                <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $row->decided_at->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $row->isApproved() ? 'bg-brand-50 text-brand-700' : 'bg-red-50 text-red-600' }}">
                                        <x-icon name="{{ $row->isApproved() ? 'check' : 'x-mark' }}" class="w-3 h-3" />
                                        {{ ucfirst($row->decision) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-medium text-slate-700">{{ $row->vipPlan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-slate-700">{{ $money($row->amount) }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $row->new_expires_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $row->decider?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>
