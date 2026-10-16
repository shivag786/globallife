<x-layouts.app title="Dashboard" heading="Dashboard">
    <div class="mb-6">
        <p class="text-body-secondary">Welcome back, <strong>{{ $user->name }}</strong>.</p>
        <p class="fs-sm text-secondary">Role: {{ $user->getRoleNames()->implode(', ') }}</p>
    </div>

    @if ($overview)
        @php
            $money = fn ($n) => '₹'.number_format((float) $n);
            $ordersUrl = route('admin.orders.index');
            $pendingUrl = route('admin.orders.index', ['status' => 'pending']);
            $deliveredUrl = route('admin.orders.index', ['status' => 'delivered']);
            $revenueUrl = $isSuperAdmin ? route('admin.revenue.index') : $ordersUrl;
        @endphp

        <div class="mb-3 d-flex align-items-center gap-2">
            <span class="d-flex h-7 w-7 align-items-center justify-content-center rounded-2 bg-brand-subtle text-brand">
                <x-icon name="sparkles" class="w-4 h-4" />
            </span>
            <h2 class="fw-semibold text-body">Sales &amp; Revenue</h2>
            <span class="fs-xs text-secondary">— your business at a glance</span>
        </div>

        {{-- Action KPIs: each box links to its related page; colour signals the action/data. --}}
        <div class="d-grid grid-cols-1 grid-cols-sm-2 grid-cols-xl-4 gap-4 mb-4">
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
        <div class="mb-3 d-flex align-items-center gap-2">
            <span class="d-flex h-7 w-7 align-items-center justify-content-center rounded-2 bg-warning-subtle text-warning">
                <x-icon name="rupee" class="w-4 h-4" />
            </span>
            <h2 class="fw-semibold text-body">Payouts</h2>
            <span class="fs-xs text-secondary">&mdash; money waiting to go out</span>
        </div>
        <div class="d-grid grid-cols-1 grid-cols-sm-2 grid-cols-xl-4 gap-4 mb-8">
            <x-stat-tile label="Withdrawal Requests from VIP Members" :value="$withdrawals['pending']"
                icon="rupee" color="amber" :href="route('admin.withdrawals.index', ['status' => 'pending'])"
                cta="Mark as paid"
                :hint="$withdrawals['pending'] > 0
                    ? '₹'.number_format($withdrawals['pending_amount'], 2).' awaiting payment'
                    : 'Nothing pending'" />
        </div>
    @endif

    @if ($stats)
        <div class="mb-3 d-flex align-items-center gap-2">
            <span class="d-flex h-7 w-7 align-items-center justify-content-center rounded-2 bg-body-secondary text-body-secondary">
                <x-icon name="users" class="w-4 h-4" />
            </span>
            <h2 class="fw-semibold text-body">Network</h2>
            <span class="fs-xs text-secondary">— your team &amp; catalogue</span>
        </div>
        <div class="d-grid grid-cols-2 grid-cols-md-3 grid-cols-xl-6 gap-4 mb-8">
            {{-- These management pages are Super Admin-only, so only link them for a Super Admin. --}}
            <x-stat-tile label="Cities" :value="$stats['cities']" icon="map-pin" color="sky"
                :href="$isSuperAdmin ? route('admin.cities.index') : null" cta="Manage cities" />
            <x-stat-tile label="Sub Admins" :value="$stats['sub_admins']" icon="users" color="brand"
                :href="$isSuperAdmin ? route('admin.sub-admins.index') : null" cta="Manage"
                hint="They run Branch Managers" />
            <x-stat-tile label="Branch Managers" :value="$stats['branch_managers']" icon="users" color="indigo"
                :href="$isSuperAdmin ? route('admin.branch-managers.index') : null" cta="Manage" />
            <x-stat-tile label="Commission Partners" :value="$stats['commission_partners']" icon="users" color="violet"
                :href="$isSuperAdmin ? route('admin.commission-partners.index') : null" cta="View" />
            {{-- The four packages are fixed, so there is no admin screen to link to. --}}
            <x-stat-tile label="Active VIP Plans" :value="$stats['vip_plans']" icon="sparkles" color="amber"
                hint="Growth, Professional, Growth Plus, Premium" />
            <x-stat-tile label="VIP Members" :value="$stats['vip_members']" icon="star" color="teal"
                :href="$isSuperAdmin ? route('admin.vip-members.index') : null" cta="View members" />
        </div>
    @endif

    @if ($charts)
        <div class="d-grid grid-cols-lg-3 gap-4 mb-4">
            <div class="col-span-lg-2 bg-body rounded-3 shadow-sm p-5 border">
                <h2 class="fw-semibold text-body mb-1">Company Revenue</h2>
                <p class="fs-xs text-secondary mb-3">Company share of activations, last 6 months</p>
                <x-apex-chart id="chart-admin-revenue" type="area" :height="300"
                    :series="[['name' => 'Company Revenue', 'data' => $charts['revenue']['data']]]"
                    :categories="$charts['revenue']['categories']" :currency="true" />
            </div>
            <div class="bg-body rounded-3 shadow-sm p-5 border">
                <h2 class="fw-semibold text-body mb-1">Commission Split</h2>
                <p class="fs-xs text-secondary mb-3">All-time payout distribution</p>
                <x-apex-chart id="chart-admin-split" type="donut" :height="300"
                    :series="[$charts['split']['partners'], $charts['split']['managers'], $charts['split']['company']]"
                    :labels="['Partners', 'Managers', 'Company']"
                    :colors="['#5fa97e', '#d4af37', '#2c704c']" :currency="true" />
            </div>
        </div>

        <div class="d-grid grid-cols-lg-2 gap-4 mb-8">
            <div class="bg-body rounded-3 shadow-sm p-5 border">
                <h2 class="fw-semibold text-body mb-1">New Users</h2>
                <p class="fs-xs text-secondary mb-3">Sign-ups per month, last 6 months</p>
                <x-apex-chart id="chart-admin-users" type="bar" :height="280"
                    :series="[['name' => 'New Users', 'data' => $charts['users']['data']]]"
                    :categories="$charts['users']['categories']" :colors="['#2c704c']" />
            </div>
            <div class="bg-body rounded-3 shadow-sm p-5 border">
                <h2 class="fw-semibold text-body mb-1">Leads</h2>
                <p class="fs-xs text-secondary mb-3">Enquiries received per month, last 6 months</p>
                <x-apex-chart id="chart-admin-leads" type="area" :height="280"
                    :series="[['name' => 'Leads', 'data' => $charts['leads']['data']]]"
                    :categories="$charts['leads']['categories']" :colors="['#d4af37']" />
            </div>
        </div>
    @endif

    {{-- A Sub Admin's own network. The platform-wide figures above are not
         theirs to see, so these count only what they own. --}}
    @if ($network)
        <div class="mb-3 d-flex align-items-center gap-2">
            <span class="d-flex h-7 w-7 align-items-center justify-content-center rounded-2 bg-body-secondary text-body-secondary">
                <x-icon name="users" class="w-4 h-4" />
            </span>
            <h2 class="fw-semibold text-body">My Network</h2>
            <span class="fs-xs text-secondary">&mdash; the Branch Managers you run</span>
        </div>
        <div class="d-grid grid-cols-1 grid-cols-sm-3 gap-4 mb-8">
            <x-stat-tile label="Branch Managers" :value="$network['branch_managers']" icon="users" color="indigo"
                :href="route('admin.branch-managers.index')" cta="Manage" />
            <x-stat-tile label="Active" :value="$network['active']" icon="sparkles" color="emerald"
                hint="Suspended accounts cannot sign in" />
            <x-stat-tile label="Commission Partners" :value="$network['commission_partners']" icon="users" color="violet"
                hint="Added by your Branch Managers" />
        </div>
    @endif

    @if ($permissionMatrix)
        <div class="bg-body rounded-3 shadow-sm p-5 border">
            <h2 class="fw-semibold mb-3">Your Permission Matrix</h2>
            <table class="w-100 fs-sm">
                <thead>
                    <tr class="text-start text-secondary">
                        <th class="py-1">Module</th>
                        <th class="py-1">Actions Granted</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissionMatrix as $module => $actions)
                        <tr class="border-top">
                            <td class="py-2 text-capitalize">{{ $module }}</td>
                            <td class="py-2">
                                @forelse ($actions as $action)
                                    <span class="d-inline-block bg-primary-subtle text-primary fs-xs px-2 py-1 rounded me-1">{{ $action }}</span>
                                @empty
                                    <span class="text-body-tertiary">none</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @unless ($stats || $permissionMatrix || $network)
        <div class="bg-body rounded-3 shadow-sm p-5 border text-secondary">
            You don't have any modules assigned yet. Contact your Super Admin.
        </div>
    @endunless
</x-layouts.app>
