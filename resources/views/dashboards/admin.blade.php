<x-layouts.app title="Dashboard" heading="Dashboard">
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong>{{ $user->name }}</strong>.</p>
        <p class="text-sm text-slate-400">Role: {{ $user->getRoleNames()->implode(', ') }}</p>
    </div>

    @if ($stats)
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Cities</p>
                <p class="text-2xl font-bold">{{ $stats['cities'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Branch Managers</p>
                <p class="text-2xl font-bold">{{ $stats['branch_managers'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Commission Partners</p>
                <p class="text-2xl font-bold">{{ $stats['commission_partners'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">Active VIP Plans</p>
                <p class="text-2xl font-bold">{{ $stats['vip_plans'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
                <p class="text-xs uppercase text-slate-400">VIP Members</p>
                <p class="text-2xl font-bold">{{ $stats['vip_members'] }}</p>
            </div>
        </div>
    @endif

    @if ($permissionMatrix)
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <h2 class="font-semibold mb-3">Your Permission Matrix</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400">
                        <th class="py-1">Module</th>
                        <th class="py-1">Actions Granted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissionMatrix as $module => $actions)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 capitalize">{{ $module }}</td>
                            <td class="py-2">
                                @forelse ($actions as $action)
                                    <span class="inline-block bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded mr-1">{{ $action }}</span>
                                @empty
                                    <span class="text-slate-300">none</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @unless ($stats || $permissionMatrix)
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
            Content modules (blog, products, leads, events, media, testimonials) ship in Phase 2.
        </div>
    @endunless
</x-layouts.app>
