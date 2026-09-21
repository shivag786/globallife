<x-layouts.app :title="'Renew '.$member->name" :heading="'Renew — '.$member->name">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 0);
        $current = $microsite->vipPlan;
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <p class="text-sm text-slate-500">
                {{ $microsite->business_name }} &middot; {{ $member->email }}
            </p>
            <p class="text-sm text-red-600 font-semibold mt-1">
                Plan expired {{ $microsite->plan_expires_at->format('d M Y') }} &mdash; their page is showing the maintenance notice.
            </p>
        </div>
        <a href="{{ route('manager.vip-members.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to VIP Members</a>
    </div>

    {{-- What they're using now. Drives the "can they even fit on this package?" call. --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold text-slate-800 mb-3">Current usage</h2>
        <div class="grid sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs uppercase text-slate-400">Previous package</p>
                <p class="font-semibold text-slate-700 mt-0.5">{{ $current->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400">Products in use</p>
                <p class="font-semibold text-slate-700 mt-0.5">
                    {{ $productQuota['used'] }}
                    <span class="text-slate-400 font-normal">/ {{ $productQuota['limit'] }} allowed</span>
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-slate-400">Services in use</p>
                <p class="font-semibold text-slate-700 mt-0.5">
                    {{ $serviceQuota['used'] }}
                    <span class="text-slate-400 font-normal">/ {{ $serviceQuota['limit'] }} allowed</span>
                </p>
            </div>
        </div>
    </div>

    <p class="text-sm text-slate-500 mb-4">
        Choose the package the member has paid for. Payment is collected offline &mdash; approving only records
        it. The package sets how long the page stays live and how many products and services they may have in
        total; anything they already added counts towards that.
    </p>

    {{-- The four packages. Each card submits its own form so the confirmation
         dialog can name the exact package being approved. --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach ($packages as $package)
            @php
                $productsFit = $productQuota['used'] <= $package->productLimit();
                $servicesFit = $serviceQuota['used'] <= $package->serviceLimit();
                $popular = $package->isMostPopular();
            @endphp
            <div class="bg-white rounded-xl border {{ $popular ? 'border-gold-400 ring-2 ring-gold-200' : 'border-slate-200' }} overflow-hidden flex flex-col">
                <div class="px-5 pt-5 pb-4 {{ $popular ? 'bg-gold-50' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-brand-900 uppercase tracking-wide text-sm">{{ $package->name }}</h3>
                        @if ($popular)
                            <span class="text-[10px] font-bold bg-gold-400 text-brand-950 px-2 py-0.5 rounded-full whitespace-nowrap">POPULAR</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-brand-900 mt-1">{{ $money($package->renewal_price) }}</p>
                    <p class="text-xs text-slate-500">{{ $package->validityLabel() }} validity</p>
                </div>

                <dl class="px-5 py-4 text-sm border-t border-slate-100 space-y-2 flex-1">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Product catalogue</dt>
                        <dd class="font-semibold {{ $productsFit ? 'text-slate-800' : 'text-red-600' }}">{{ $package->productLimit() }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Services</dt>
                        <dd class="font-semibold {{ $servicesFit ? 'text-slate-800' : 'text-red-600' }}">{{ $package->serviceLimit() }}</dd>
                    </div>

                    @if (! $productsFit || ! $servicesFit)
                        <p class="text-xs text-red-600 pt-1">
                            Below current usage &mdash; they keep what they have but can add no more.
                        </p>
                    @else
                        <p class="text-xs text-slate-400 pt-1">
                            Room for {{ $package->productLimit() - $productQuota['used'] }} more products,
                            {{ $package->serviceLimit() - $serviceQuota['used'] }} more services.
                        </p>
                    @endif
                </dl>

                <form method="POST" action="{{ route('manager.vip-members.renewal.approve', $member) }}" class="px-5 pb-5"
                      data-confirm="Confirm {{ $money($package->renewal_price) }} received for the {{ $package->name }} package? {{ $member->name }}'s page goes live for {{ $package->validityLabel() }} with {{ $package->productLimit() }} products and {{ $package->serviceLimit() }} services allowed."
                      data-confirm-title="Approve {{ $package->name }} renewal"
                      data-confirm-button="Yes, approve">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="vip_plan_id" value="{{ $package->id }}">
                    <button type="submit" class="w-full bg-green-600 text-white text-sm font-semibold px-4 py-2.5 rounded-md hover:bg-green-700">
                        Approve {{ $package->name }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    {{-- Reject, with its own confirmation. --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-slate-800">Reject this renewal</h2>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">
                    Records the refusal and leaves the page on the maintenance notice. Their content, leads and
                    reviews are kept, and you can approve a package here later.
                </p>
            </div>
            <form method="POST" action="{{ route('manager.vip-members.renewal.reject', $member) }}"
                  data-confirm="Reject {{ $member->name }}'s renewal? Their page stays on the maintenance notice until you approve a package."
                  data-confirm-title="Reject renewal" data-confirm-button="Yes, reject" data-confirm-danger>
                @csrf
                @method('PATCH')
                <button type="submit" class="bg-white border border-red-300 text-red-600 text-sm font-semibold px-4 py-2.5 rounded-md hover:bg-red-50">
                    Reject renewal
                </button>
            </form>
        </div>
    </div>

    @if ($history->isNotEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden mt-8">
            <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">Renewal history</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Decision</th>
                            <th class="px-4 py-3">Package</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3">Valid until</th>
                            <th class="px-4 py-3">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $row->decided_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $row->isApproved() ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                        {{ $row->decision }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $row->vipPlan?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $money($row->amount) }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $row->new_expires_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $row->decider?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.app>
