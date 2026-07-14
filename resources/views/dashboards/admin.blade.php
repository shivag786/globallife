<x-layouts.app title="Dashboard" heading="Dashboard">
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong>{{ $user->name }}</strong>.</p>
        <p class="text-sm text-slate-400">Role: {{ $user->getRoleNames()->implode(', ') }}</p>
    </div>

    @if ($stats)
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            @foreach ([
                ['Cities', $stats['cities']],
                ['Branch Managers', $stats['branch_managers']],
                ['Commission Partners', $stats['commission_partners']],
                ['Active VIP Plans', $stats['vip_plans']],
                ['VIP Members', $stats['vip_members']],
            ] as [$label, $value])
                <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 transition hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</p>
                    <p class="text-2xl font-bold text-slate-800" data-countup="{{ $value }}">0</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($charts)
        <div class="grid lg:grid-cols-3 gap-4 mb-4">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">Company Revenue</h2>
                <p class="text-xs text-slate-400 mb-3">Company share of activations, last 6 months</p>
                <x-apex-chart id="chart-admin-revenue" type="area" :height="300"
                    :series="[['name' => 'Company Revenue', 'data' => $charts['revenue']['data']]]"
                    :categories="$charts['revenue']['categories']" :currency="true" />
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">Commission Split</h2>
                <p class="text-xs text-slate-400 mb-3">All-time payout distribution</p>
                <x-apex-chart id="chart-admin-split" type="donut" :height="300"
                    :series="[$charts['split']['partners'], $charts['split']['managers'], $charts['split']['company']]"
                    :labels="['Partners', 'Managers', 'Company']"
                    :colors="['#5fa97e', '#d4af37', '#2c704c']" :currency="true" />
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">New Users</h2>
                <p class="text-xs text-slate-400 mb-3">Sign-ups per month, last 6 months</p>
                <x-apex-chart id="chart-admin-users" type="bar" :height="280"
                    :series="[['name' => 'New Users', 'data' => $charts['users']['data']]]"
                    :categories="$charts['users']['categories']" :colors="['#2c704c']" />
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
                <h2 class="font-semibold text-slate-800 mb-1">Leads</h2>
                <p class="text-xs text-slate-400 mb-3">Enquiries received per month, last 6 months</p>
                <x-apex-chart id="chart-admin-leads" type="area" :height="280"
                    :series="[['name' => 'Leads', 'data' => $charts['leads']['data']]]"
                    :categories="$charts['leads']['categories']" :colors="['#d4af37']" />
            </div>
        </div>
    @endif

    @if ($permissionMatrix)
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
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
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 text-slate-500">
            You don't have any modules assigned yet. Contact your Super Admin.
        </div>
    @endunless
</x-layouts.app>
