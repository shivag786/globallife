<x-layouts.app :title="'Renew '.$member->name" :heading="'Renew Membership'">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 0);
        $current = $microsite->vipPlan;
        $lapsedDays = abs($microsite->daysUntilExpiry() ?? 0);
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

            <div class="flex items-center gap-2 bg-red-500/15 border border-red-400/30 rounded-xl px-4 py-3">
                <x-icon name="x-mark" class="w-5 h-5 text-red-300 flex-shrink-0" />
                <div>
                    <p class="text-sm font-semibold text-red-200">Expired {{ $microsite->plan_expires_at->format('d M Y') }}</p>
                    <p class="text-xs text-red-300/80">
                        {{ $lapsedDays }} day{{ $lapsedDays === 1 ? '' : 's' }} ago &middot; page showing maintenance notice
                    </p>
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
                Payment is collected offline &mdash; approving records it and brings their page straight back.
                The package sets how long the page stays live and the total products and services they may
                have; anything already added counts towards it.
            </p>
        </div>
        <a href="{{ route('manager.vip-members.index') }}" class="text-sm text-brand-700 hover:underline whitespace-nowrap">
            &larr; Back to VIP Members
        </a>
    </div>

    {{-- Each card is its own form, so the confirmation dialog can name the exact
         package, price, validity and caps being approved. --}}
    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10 items-start">
        @foreach ($packages as $package)
            @php
                $popular = $package->isMostPopular();
                $productsFit = $productQuota['used'] <= $package->productLimit();
                $servicesFit = $serviceQuota['used'] <= $package->serviceLimit();
                $fits = $productsFit && $servicesFit;
                $isCurrent = $current && $current->id === $package->id;
            @endphp

            <div class="relative bg-white rounded-2xl flex flex-col premium-shadow transition hover:-translate-y-1
                        {{ $popular ? 'border-2 border-gold-500' : 'border border-slate-200' }}">
                @if ($popular)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gold-500 text-brand-950 text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full whitespace-nowrap shadow">
                        Most Popular
                    </span>
                @endif

                {{-- Name + price --}}
                <div class="px-6 pt-7 pb-5 text-center border-b border-slate-100">
                    <h4 class="font-display text-lg font-bold text-brand-900 uppercase tracking-wide">{{ $package->name }}</h4>
                    @if ($isCurrent)
                        <p class="text-[11px] text-slate-400 mt-0.5">their previous package</p>
                    @endif
                    <p class="mt-3">
                        <span class="font-display text-4xl font-extrabold text-brand-800">{{ $money($package->renewal_price) }}</span>
                    </p>
                    <p class="inline-flex items-center gap-1.5 mt-2 bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1 rounded-full">
                        <x-icon name="calendar" class="w-3.5 h-3.5" />
                        {{ $package->validityLabel() }} validity
                    </p>
                </div>

                {{-- What it buys --}}
                <div class="px-6 py-5 flex-1 space-y-3 text-sm">
                    <div class="flex items-center gap-2.5">
                        <x-icon name="{{ $productsFit ? 'check-circle' : 'x-mark' }}"
                                class="w-4 h-4 flex-shrink-0 {{ $productsFit ? 'text-brand-500' : 'text-red-500' }}" />
                        <span class="text-slate-600">
                            <span class="font-bold text-brand-900">{{ $package->productLimit() }}</span> product catalogue
                        </span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <x-icon name="{{ $servicesFit ? 'check-circle' : 'x-mark' }}"
                                class="w-4 h-4 flex-shrink-0 {{ $servicesFit ? 'text-brand-500' : 'text-red-500' }}" />
                        <span class="text-slate-600">
                            <span class="font-bold text-brand-900">{{ $package->serviceLimit() }}</span> services
                        </span>
                    </div>

                    @if ($fits)
                        <p class="text-xs text-slate-400 pt-1 border-t border-slate-100 mt-3">
                            Room for {{ $package->productLimit() - $productQuota['used'] }} more products and
                            {{ $package->serviceLimit() - $serviceQuota['used'] }} more services.
                        </p>
                    @else
                        <p class="text-xs text-red-600 pt-2 border-t border-red-100 mt-3 flex items-start gap-1.5">
                            <x-icon name="x-mark" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" />
                            <span>Below what they already use. They keep everything, but can add no more.</span>
                        </p>
                    @endif
                </div>

                <div class="px-6 pb-6">
                    <form method="POST" action="{{ route('manager.vip-members.renewal.approve', $member) }}"
                          data-confirm="Confirm {{ $money($package->renewal_price) }} received for the {{ $package->name }} package? {{ $member->name }}'s page goes live for {{ $package->validityLabel() }} with {{ $package->productLimit() }} products and {{ $package->serviceLimit() }} services allowed."
                          data-confirm-title="Approve {{ $package->name }} renewal"
                          data-confirm-button="Yes, approve">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="vip_plan_id" value="{{ $package->id }}">
                        <button type="submit"
                                class="w-full py-3 rounded-full font-semibold text-sm transition
                                       {{ $popular
                                            ? 'bg-gold-500 text-brand-950 hover:bg-gold-400'
                                            : 'bg-brand-700 text-white hover:bg-brand-800' }}">
                            Approve &amp; Renew
                        </button>
                    </form>
                </div>
            </div>
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
