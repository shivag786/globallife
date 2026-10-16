<x-layouts.app title="VIP Members" heading="VIP Members">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('manager.vip-members.create') }}" class="btn btn-brand btn-sm">
            + Add VIP Member
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Account</th>
                    <th class="px-4 py-3">Activation</th>
                    <th class="px-4 py-3">Plan Validity</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $member->name }}</td>
                        <td class="px-4 py-3">{{ $member->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $member->vipMicrosite->city->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $member->vipMicrosite->vipPlan->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $member->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                {{ $member->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($member->vipMicrosite?->isActivated())
                                <span class="px-2 py-1 rounded fs-xs bg-brand-subtle text-brand" title="{{ $member->vipMicrosite->activated_at }}">
                                    Activated {{ $member->vipMicrosite->activated_at->format('d M Y') }}
                                </span>
                            @else
                                {{-- New members are activated as they are created, so this
                                     only shows for accounts added before that change. --}}
                                <form action="{{ route('manager.vip-members.activate', $member) }}" method="POST" class="d-inline"
                                      data-confirm="Confirm payment received? This records the commission split and cannot be undone."
                                      data-confirm-title="Activate VIP Member" data-confirm-button="Yes, activate">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-gold btn-sm rounded-pill text-brand-emphasis fs-xs">
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </td>
                        {{-- Plan validity. Approve / Reject appear only once the plan has expired. --}}
                        <td class="px-4 py-3">
                            @php $site = $member->vipMicrosite; @endphp
                            @if (! $site?->isActivated())
                                <span class="fs-xs text-secondary">Not activated</span>
                            @elseif ($site->isRenewalDue())
                                @php $daysLeft = $site->daysUntilExpiry(); @endphp
                                @if ($site->hasExpiredPlan())
                                    <p class="fs-xs fw-semibold text-danger mb-2">
                                        Expired {{ $site->plan_expires_at->format('d M Y') }}
                                    </p>
                                @else
                                    <p class="fs-xs fw-semibold text-warning mb-2">
                                        Expires {{ $site->plan_expires_at->format('d M Y') }}
                                        &middot; {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left
                                    </p>
                                @endif
                                {{-- Renewals open 30 days out, so a page need never go
                                     dark while payment is being collected. Approve/Reject
                                     live on the renewal screen, where the partner picks
                                     which package was paid for. --}}
                                <a href="{{ route('manager.vip-members.renewal', $member) }}"
                                   class="btn btn-gold btn-sm rounded-pill d-inline-block text-brand-emphasis fs-xs">
                                    {{ $site->hasExpiredPlan() ? 'Renew — choose package' : 'Renew early' }}
                                </a>
                                @if ($last = $site->renewals->first())
                                    <p class="fs-xs text-secondary mt-2">
                                        Last decision: {{ $last->decision }} on {{ $last->decided_at->format('d M Y') }}
                                    </p>
                                @endif
                            @else
                                <span class="px-2 py-1 rounded fs-xs bg-success-subtle text-success">
                                    Valid till {{ $site->plan_expires_at?->format('d M Y') ?? '—' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            @if ($member->vipMicrosite)
                                <a href="{{ $member->vipMicrosite->publicPath() }}" target="_blank" class="text-brand hover-underline">View Page</a>
                            @endif
                            <a href="{{ route('manager.vip-members.edit', $member) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('manager.vip-members.toggle-status', $member) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-warning hover-underline">
                                    {{ $member->status === 'active' ? 'Suspend' : 'Reactivate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-secondary">You haven't added any VIP Members yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
