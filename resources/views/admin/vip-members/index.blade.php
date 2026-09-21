<x-layouts.app title="VIP Members" heading="VIP Members">
    @php $money = fn ($n) => '₹'.number_format((float) $n); @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            Every VIP member with their plan renewal, upline, and the commission earned on their activation.
        </p>
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email or business"
                   class="w-64 max-w-full rounded-full border-slate-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            <button type="submit" class="rounded-full bg-brand-700 text-white text-sm px-4 py-2 hover:bg-brand-800 transition">Search</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">VIP Member</th>
                    <th class="px-4 py-3 font-medium">Plan &amp; Renewal</th>
                    <th class="px-4 py-3 font-medium">Upline &amp; Commission</th>
                    <th class="px-4 py-3 font-medium text-right">Site</th>
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
                    <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                        {{-- 1. Member + business details in one column --}}
                        <td class="px-4 py-3 align-top">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold overflow-hidden">
                                    @if ($m->logo_path)
                                        <img src="{{ asset('storage/'.$m->logo_path) }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($m->business_name ?: $u?->name ?: '?', 0, 1)) }}
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 truncate">{{ $m->business_name ?: '—' }}</p>
                                    <p class="text-slate-600 truncate">{{ $u?->name ?? 'No owner' }}</p>
                                    <p class="text-xs text-slate-400 truncate">
                                        {{ $u?->email }}@if ($m->phone_number) · {{ $m->phone_number }}@endif
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $m->city?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- 2. Plan + renewal date + status --}}
                        <td class="px-4 py-3 align-top">
                            <p class="font-medium text-slate-800">{{ $m->vipPlan?->name ?? '—' }}</p>
                            @if ($renewsAt)
                                <p class="text-xs {{ $overdue ? 'text-red-600 font-medium' : 'text-slate-500' }} mt-0.5">
                                    {{ $overdue ? 'Renewal overdue' : 'Renews' }} {{ $renewsAt->format('d M Y') }}
                                </p>
                            @else
                                <p class="text-xs text-slate-400 mt-0.5">Not activated</p>
                            @endif
                            <span class="mt-1.5 inline-block px-2 py-0.5 rounded-full text-xs {{ $m->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($m->status) }}
                            </span>
                        </td>

                        {{-- 3. Upline (CP + Branch Manager) with commission amounts in small font --}}
                        <td class="px-4 py-3 align-top">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-violet-50 text-violet-600 text-[0.65rem] font-semibold uppercase tracking-wide">CP</span>
                                        <span class="text-slate-700">{{ $cpName ?? '—' }}</span>
                                    </span>
                                    @if ($tx)<span class="text-xs text-slate-400 whitespace-nowrap">{{ $money($tx->commission_partner_amount) }}</span>@endif
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 text-[0.65rem] font-semibold uppercase tracking-wide">BM</span>
                                        <span class="text-slate-700">{{ $bmName ?? '—' }}</span>
                                    </span>
                                    @if ($tx)<span class="text-xs text-slate-400 whitespace-nowrap">{{ $money($tx->branch_manager_amount) }}</span>@endif
                                </div>
                                @if ($tx)
                                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-1">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[0.65rem] font-semibold uppercase tracking-wide">Co.</span>
                                            <span class="text-slate-500 text-xs">Company share</span>
                                        </span>
                                        <span class="text-xs text-slate-400 whitespace-nowrap">{{ $money($tx->company_amount) }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- 4. Visit the live site --}}
                        <td class="px-4 py-3 align-top text-right">
                            <a href="{{ url($m->publicPath()) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-full border border-brand-200 text-brand-700 text-xs px-3 py-1.5 hover:bg-brand-50 transition">
                                <x-icon name="eye" class="w-4 h-4" /> Visit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No VIP members found.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">
        {{ $members->links() }}
    </div>
</x-layouts.app>
