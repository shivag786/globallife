<x-layouts.app title="VIP Members" heading="VIP Members">
    @php $money = fn ($n) => '₹'.number_format((float) $n); @endphp

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <p class="fs-sm text-secondary">
            Every VIP member with their plan renewal, upline, and the commission earned on their activation.
        </p>
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email or business"
                   class="w-64 mw-100 rounded-pill fs-sm">
            <button type="submit" class="btn btn-brand btn-sm rounded-pill">Search</button>
        </form>
    </div>

    <div class="bg-body rounded-4 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3 fw-medium">VIP Member</th>
                    <th class="px-4 py-3 fw-medium">Plan &amp; Renewal</th>
                    <th class="px-4 py-3 fw-medium">Upline &amp; Commission</th>
                    <th class="px-4 py-3 fw-medium text-end">Site</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $m)
                    @php
                        $u = $m->user;
                        $tx = $m->commissionTransaction;
                        $renewsAt = $m->plan_expires_at;
                        $overdue = $renewsAt && $renewsAt->isPast();
                        // Upline: prefer the recorded activation split, fall back to the creator chain.
                        $cpName = $tx?->commissionPartner?->name ?? $u?->creator?->name;
                        $bmName = $tx?->branchManager?->name ?? $u?->creator?->creator?->name;
                    @endphp
                    <tr class="border-top">
                        {{-- 1. Member + business details in one column --}}
                        <td class="px-4 py-3 align-top">
                            <div class="d-flex align-items-start gap-3">
                                <span class="d-flex h-10 w-10 flex-shrink-0 align-items-center justify-content-center rounded-pill bg-brand-subtle text-brand fw-bold overflow-hidden">
                                    @if ($m->logo_path)
                                        <img src="{{ asset('storage/'.$m->logo_path) }}" alt="" class="h-100 w-100 object-fit-cover">
                                    @else
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($m->business_name ?: $u?->name ?: '?', 0, 1)) }}
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="fw-semibold text-body text-truncate">{{ $m->business_name ?: '—' }}</p>
                                    <p class="text-body-secondary text-truncate">{{ $u?->name ?? 'No owner' }}</p>
                                    <p class="fs-xs text-secondary text-truncate">
                                        {{ $u?->email }}@if ($m->phone_number) · {{ $m->phone_number }}@endif
                                    </p>
                                    <p class="fs-xs text-secondary">{{ $m->city?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- 2. Plan + renewal date + status --}}
                        <td class="px-4 py-3 align-top">
                            <p class="fw-medium text-body">{{ $m->vipPlan?->name ?? '—' }}</p>
                            @if ($renewsAt)
                                <p class="fs-xs {{ $overdue ? 'text-danger fw-medium' : 'text-secondary' }} mt-1">
                                    {{ $overdue ? 'Renewal overdue' : 'Renews' }} {{ $renewsAt->format('d M Y') }}
                                </p>
                            @else
                                <p class="fs-xs text-secondary mt-1">Not activated</p>
                            @endif
                            <span class="mt-2 d-inline-block px-2 py-1 rounded-pill fs-xs {{ $m->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ ucfirst($m->status) }}
                            </span>
                        </td>

                        {{-- 3. Upline (CP + Branch Manager) with commission amounts in small font --}}
                        <td class="px-4 py-3 align-top">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <span class="px-2 py-1 rounded bg-primary-subtle text-primary fw-semibold text-uppercase tracking-wide">CP</span>
                                        <span class="text-body-secondary">{{ $cpName ?? '—' }}</span>
                                    </span>
                                    @if ($tx)<span class="fs-xs text-secondary text-nowrap">{{ $money($tx->commission_partner_amount) }}</span>@endif
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <span class="px-2 py-1 rounded bg-primary-subtle text-primary fw-semibold text-uppercase tracking-wide">BM</span>
                                        <span class="text-body-secondary">{{ $bmName ?? '—' }}</span>
                                    </span>
                                    @if ($tx)<span class="fs-xs text-secondary text-nowrap">{{ $money($tx->branch_manager_amount) }}</span>@endif
                                </div>
                                @if ($tx)
                                    <div class="d-flex align-items-center justify-content-between gap-3 border-top pt-1">
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <span class="px-2 py-1 rounded bg-success-subtle text-success fw-semibold text-uppercase tracking-wide">Co.</span>
                                            <span class="text-secondary fs-xs">Company share</span>
                                        </span>
                                        <span class="fs-xs text-secondary text-nowrap">{{ $money($tx->company_amount) }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- 4. Visit the live site --}}
                        <td class="px-4 py-3 align-top text-end">
                            <a href="{{ url($m->publicPath()) }}" target="_blank" rel="noopener"
                               class="d-inline-flex align-items-center gap-2 rounded-pill border border-brand-subtle text-brand fs-xs px-3 py-2">
                                <x-icon name="eye" class="w-4 h-4" /> Visit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-secondary">No VIP members found.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">
        {{ $members->links() }}
    </div>
</x-layouts.app>
