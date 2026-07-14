<x-layouts.app title="VIP Members" heading="VIP Members">
    <div class="flex justify-end mb-4">
        <a href="{{ route('manager.vip-members.create') }}" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">
            + Add VIP Member
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Business</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Account</th>
                    <th class="px-4 py-3">Activation</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $member->name }}</td>
                        <td class="px-4 py-3">{{ $member->vipMicrosite->business_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $member->vipMicrosite->city->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $member->vipMicrosite->vipPlan->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $member->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                {{ $member->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($member->vipMicrosite?->isActivated())
                                <span class="px-2 py-0.5 rounded text-xs bg-brand-50 text-brand-700" title="{{ $member->vipMicrosite->activated_at }}">
                                    Activated {{ $member->vipMicrosite->activated_at->format('d M Y') }}
                                </span>
                            @else
                                <form action="{{ route('manager.vip-members.activate', $member) }}" method="POST" class="inline"
                                      data-confirm="Confirm payment received? This records the commission split and cannot be undone."
                                      data-confirm-title="Activate VIP Member" data-confirm-button="Yes, activate">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-gold-500 text-brand-950 text-xs font-semibold px-3 py-1.5 rounded-full hover:bg-gold-400">
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            @if ($member->vipMicrosite)
                                <a href="{{ $member->vipMicrosite->publicPath() }}" target="_blank" class="text-brand-700 hover:underline">View Page</a>
                            @endif
                            <a href="{{ route('manager.vip-members.edit', $member) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('manager.vip-members.toggle-status', $member) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-amber-600 hover:underline">
                                    {{ $member->status === 'active' ? 'Suspend' : 'Reactivate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400">You haven't added any VIP Members yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
