<x-layouts.app title="Dashboard" heading="Dashboard">
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong>{{ $user->name }}</strong>.</p>
        <p class="text-sm text-slate-400">Role: {{ $user->getRoleNames()->implode(', ') }}</p>
    </div>

    @if ($overview)
        @php
            $money = fn ($n) => '₹'.number_format((float) $n);
            $ordersUrl = route('admin.orders.index');
            $pendingUrl = route('admin.orders.index', ['status' => 'pending']);
            $deliveredUrl = route('admin.orders.index', ['status' => 'delivered']);
            $revenueUrl = $isSuperAdmin ? route('admin.revenue.index') : $ordersUrl;
        @endphp

        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                <x-icon name="sparkles" class="w-4 h-4" />
            </span>
            <h2 class="font-semibold text-slate-800">Sales &amp; Revenue</h2>
            <span class="text-xs text-slate-400">— your business at a glance</span>
        </div>

        {{-- Action KPIs: each box links to its related page; colour signals the action/data. --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
            <x-stat-tile label="Orders Today" :value="$overview['orders_today']" icon="shopping-bag"
                color="indigo" :href="$ordersUrl" cta="View orders"
                :hint="$money($overview['revenue_today']).' placed today'" />

            <x-stat-tile label="Pending Orders" :value="$overview['pending_orders']" icon="truck"
                color="amber" :href="$pendingUrl" cta="Process orders"
                :hint="$overview['pending_orders'] > 0 ? 'Awaiting your action' : 'All caught up'" />

            <x-stat-tile label="Company Commission" :value="$overview['company_commission']" money icon="rupee"
                color="emerald" :href="$revenueUrl" cta="See revenue" hint="Your realised earnings" />

            <x-stat-tile label="Commission Pipeline" :value="$overview['pending_commission']" money icon="sparkles"
                color="violet" :href="$pendingUrl" cta="View pending" hint="Unlocks as orders deliver" />
        </div>

        {{-- Revenue flow: the two streams that make up company income, side by side. --}}
        <x-revenue-flow
            :vip="$overview['vip']['revenue']" :product="$overview['products']['revenue']"
            :total="$overview['company_commission']"
            :vip-href="$revenueUrl" :product-href="$deliveredUrl"
            vip-label="Revenue from VIP Plans"
            total-label="Total Company Revenue"
            subheading="the two streams that make up company income"
            :vip-hint="'Company share of '.$overview['vip']['count'].' plan'.($overview['vip']['count'] === 1 ? '' : 's').' sold'"
            :product-hint="'Company margin on '.$overview['products']['count'].' delivered order'.($overview['products']['count'] === 1 ? '' : 's')" />
    @endif

    @if ($withdrawals)
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                <x-icon name="rupee" class="w-4 h-4" />
            </span>
            <h2 class="font-semibold text-slate-800">Payouts</h2>
            <span class="text-xs text-slate-400">&mdash; money waiting to go out</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            <x-stat-tile label="Withdrawal Requests from VIP Members" :value="$withdrawals['pending']"
                icon="rupee" color="amber" :href="route('admin.withdrawals.index', ['status' => 'pending'])"
                cta="Mark as paid"
                :hint="$withdrawals['pending'] > 0
                    ? '₹'.number_format($withdrawals['pending_amount'], 2).' awaiting payment'
                    : 'Nothing pending'" />
        </div>
    @endif

    @if ($stats)
        <div class="mb-3 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <x-icon name="users" class="w-4 h-4" />
            </span>
            <h2 class="font-semibold text-slate-800">Network</h2>
            <span class="text-xs text-slate-400">— your team &amp; catalogue</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            {{-- These management pages are Super Admin-only, so only link them for a Super Admin. --}}
            <x-stat-tile label="Cities" :value="$stats['cities']" icon="map-pin" color="sky"
                :href="$isSuperAdmin ? route('admin.cities.index') : null" cta="Manage cities" />
            <x-stat-tile label="Branch Managers" :value="$stats['branch_managers']" icon="users" color="indigo"
                :href="$isSuperAdmin ? route('admin.branch-managers.index') : null" cta="Manage" />
            <x-stat-tile label="Commission Partners" :value="$stats['commission_partners']" icon="users" color="violet"
                :href="$isSuperAdmin ? route('admin.commission-partners.index') : null" cta="View" />
            <x-stat-tile label="Active VIP Plans" :value="$stats['vip_plans']" icon="sparkles" color="amber"
                :href="$isSuperAdmin ? route('admin.vip-plans.index') : null" cta="Manage plans" />
            <x-stat-tile label="VIP Members" :value="$stats['vip_members']" icon="star" color="teal"
                :href="$isSuperAdmin ? route('admin.vip-members.index') : null" cta="View members" />
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
