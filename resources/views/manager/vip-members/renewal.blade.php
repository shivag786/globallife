<x-layouts.app :title="'Renew '.$member->name" :heading="'Renew Membership'">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 0);
        $current = $microsite->vipPlan;
        $daysLeft = $microsite->daysUntilExpiry() ?? 0;
        $expired = $microsite->hasExpiredPlan();
    @endphp

    {{-- Who and why, with the lapse stated plainly up top. --}}
    <div class="rounded-4 overflow-hidden premium-shadow mb-6" style="background: linear-gradient(135deg,#1b3c2c,#0d2118);">
        <div class="px-6 py-5 px-sm-8 py-sm-6 d-flex flex-wrap align-items-start justify-content-between gap-5">
            <div class="min-w-0">
                <p class="fs-xs text-uppercase tracking-widest text-gold fw-semibold">Membership Renewal</p>
                <h2 class="font-display fs-2xl fs-3xl fw-bold text-white mt-1 text-truncate">{{ $member->name }}</h2>
                <p class="fs-sm text-brand mt-1 text-truncate">
                    {{ $microsite->business_name }}
                    @if ($microsite->city) &middot; {{ $microsite->city->name }} @endif
                    &middot; {{ $member->email }}
                </p>
            </div>

            {{-- Two states: still live but due, or already lapsed and offline. --}}
            <div class="d-flex align-items-center gap-2 rounded-3 px-4 py-3 border {{ $expired ? 'bg-danger-subtle border' : 'bg-gold-subtle border-gold-subtle' }}">
                <x-icon name="{{ $expired ? 'x-mark' : 'calendar' }}"
                        class="w-5 h-5 flex-shrink-0 {{ $expired ? 'text-danger' : 'text-gold' }}" />
                <div>
                    @if ($expired)
                        <p class="fs-sm fw-semibold text-danger">
                            Expired {{ $microsite->plan_expires_at->format('d M Y') }}
                        </p>
                        <p class="fs-xs">
                            {{ abs($daysLeft) }} day{{ abs($daysLeft) === 1 ? '' : 's' }} ago &middot; page showing maintenance notice
                        </p>
                    @else
                        <p class="fs-sm fw-semibold text-gold">
                            Expires {{ $microsite->plan_expires_at->format('d M Y') }}
                        </p>
                        <p class="fs-xs text-brand">
                            in {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} &middot; page still live
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Usage strip: the numbers that decide which package actually fits. --}}
        <div class="d-grid grid-cols-1 grid-cols-sm-3 border-top border-white border-opacity-10 divide-y divide-x">
            <div class="px-6 py-4">
                <p class="text-uppercase tracking-wider text-brand">Previous package</p>
                <p class="text-white fw-semibold mt-1 text-truncate">{{ $current->name ?? '—' }}</p>
            </div>
            <div class="px-6 py-4">
                <p class="text-uppercase tracking-wider text-brand">Products in use</p>
                <p class="text-white fw-semibold mt-1">
                    {{ $productQuota['used'] }}
                    <span class="text-brand fw-normal fs-sm">/ {{ $productQuota['limit'] }}</span>
                </p>
            </div>
            <div class="px-6 py-4">
                <p class="text-uppercase tracking-wider text-brand">Services in use</p>
                <p class="text-white fw-semibold mt-1">
                    {{ $serviceQuota['used'] }}
                    <span class="text-brand fw-normal fs-sm">/ {{ $serviceQuota['limit'] }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-5">
        <div>
            <h3 class="font-display fs-xl fw-bold text-brand-emphasis">Choose the package they paid for</h3>
            <p class="fs-sm text-secondary mt-1 mw-2xl">
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
        <a href="{{ route('manager.vip-members.index') }}" class="fs-sm text-brand hover-underline text-nowrap">
            &larr; Back to VIP Members
        </a>
    </div>

    {{-- Each card is its own form, so the confirmation dialog can name the exact
         package, price, validity and caps being approved. --}}
    <div class="d-grid grid-cols-sm-2 grid-cols-xl-4 gap-6 mb-10 align-items-stretch">
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
                            class="w-100 py-3 rounded-pill fw-semibold fs-sm {{ $package->isMostPopular()
                                        ? 'bg-gold text-brand-emphasis'
                                        : 'bg-brand text-white' }}">
                        Approve &amp; Renew
                    </button>
                </form>
            </x-package-card>
        @endforeach
    </div>

    {{-- At-a-glance comparison, the same shape as the printed package sheet. --}}
    <div class="bg-body rounded-4 border overflow-hidden mb-10">
        <div class="px-6 py-4 border-bottom">
            <h3 class="font-display fs-lg fw-bold text-brand-emphasis">Compare packages</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead>
                    <tr class="bg-brand text-white text-start">
                        <th class="px-5 py-3 fw-semibold">Feature</th>
                        @foreach ($packages as $package)
                            <th class="px-5 py-3 fw-semibold text-center text-nowrap">
                                {{ strtoupper($package->name) }}
                                <span class="d-block fw-normal text-gold fs-xs mt-1">{{ $money($package->renewal_price) }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-bottom">
                        <th class="px-5 py-3 text-start fw-semibold text-body-secondary bg-body-tertiary">Validity</th>
                        @foreach ($packages as $package)
                            <td class="px-5 py-3 text-center text-body-secondary">{{ $package->validityLabel() }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-bottom">
                        <th class="px-5 py-3 text-start fw-semibold text-body-secondary bg-body-tertiary">Product catalogue</th>
                        @foreach ($packages as $package)
                            <td class="px-5 py-3 text-center fw-semibold {{ $productQuota['used'] <= $package->productLimit() ? 'text-brand-emphasis' : 'text-danger' }}">
                                {{ $package->productLimit() }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="px-5 py-3 text-start fw-semibold text-body-secondary bg-body-tertiary">Services</th>
                        @foreach ($packages as $package)
                            <td class="px-5 py-3 text-center fw-semibold {{ $serviceQuota['used'] <= $package->serviceLimit() ? 'text-brand-emphasis' : 'text-danger' }}">
                                {{ $package->serviceLimit() }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="px-5 py-3 fs-xs text-secondary border-top">
            Figures in red sit below this member's current usage of
            {{ $productQuota['used'] }} product{{ $productQuota['used'] === 1 ? '' : 's' }} and
            {{ $serviceQuota['used'] }} service{{ $serviceQuota['used'] === 1 ? '' : 's' }}.
        </p>
    </div>

    {{-- Reject, kept visually quiet and well away from the approve buttons. --}}
    <div class="bg-body rounded-4 border p-6 mb-10">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
            <div>
                <h3 class="fw-semibold text-body">Not renewing?</h3>
                <p class="fs-sm text-secondary mt-1 mw-xl">
                    Records the refusal and leaves the page on the maintenance notice. Their content, leads and
                    reviews are all kept, and you can approve a package here whenever they pay.
                </p>
            </div>
            <form method="POST" action="{{ route('manager.vip-members.renewal.reject', $member) }}"
                  data-confirm="Reject {{ $member->name }}'s renewal? Their page stays on the maintenance notice until you approve a package."
                  data-confirm-title="Reject renewal" data-confirm-button="Yes, reject" data-confirm-danger>
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-link p-0 text-decoration-none border border-danger-subtle text-danger fs-sm fw-semibold px-5 py-2 rounded-pill text-nowrap">
                    Reject renewal
                </button>
            </form>
        </div>
    </div>

    @if ($history->isNotEmpty())
        <div class="bg-body rounded-4 border overflow-hidden">
            <div class="px-6 py-4 border-bottom">
                <h3 class="font-display fs-lg fw-bold text-brand-emphasis">Renewal history</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-100 fs-sm">
                    <thead class="bg-body-tertiary text-start text-secondary">
                        <tr>
                            <th class="px-5 py-3 fw-medium">Date</th>
                            <th class="px-5 py-3 fw-medium">Decision</th>
                            <th class="px-5 py-3 fw-medium">Package</th>
                            <th class="px-5 py-3 fw-medium text-end">Amount</th>
                            <th class="px-5 py-3 fw-medium">Valid until</th>
                            <th class="px-5 py-3 fw-medium">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr class="border-top">
                                <td class="px-5 py-3 text-secondary text-nowrap">{{ $row->decided_at->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <span class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill fs-xs fw-medium {{ $row->isApproved() ? 'bg-brand-subtle text-brand' : 'bg-danger-subtle text-danger' }}">
                                        <x-icon name="{{ $row->isApproved() ? 'check' : 'x-mark' }}" class="w-3 h-3" />
                                        {{ ucfirst($row->decision) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 fw-medium text-body-secondary">{{ $row->vipPlan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-end font-monospace text-body-secondary">{{ $money($row->amount) }}</td>
                                <td class="px-5 py-3 text-secondary">{{ $row->new_expires_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-secondary">{{ $row->decider?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>
