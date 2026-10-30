<x-layouts.app title="Dashboard" heading="Dashboard">
    @php
        $roleLabel = $user->getRoleNames()->map(fn ($r) => \Illuminate\Support\Str::headline($r))->implode(', ');
    @endphp

    <x-dash-hero :name="$user->name" :eyebrow="$roleLabel" subtitle="Here's how Global Life is doing today.">
        <x-slot:meta>
            <span class="dash-hero__chip"><x-icon name="shield-check" class="w-3.5 h-3.5" /> Role: {{ $roleLabel }}</span>
            @if ($overview)
                <span class="dash-hero__chip"><x-icon name="shopping-bag" class="w-3.5 h-3.5" /> {{ number_format($overview['orders_today']) }} orders today</span>
                <span class="dash-hero__chip"><x-icon name="truck" class="w-3.5 h-3.5" /> {{ number_format($overview['pending_orders']) }} pending</span>
            @endif
        </x-slot:meta>
        @if ($overview)
            <x-slot:actions>
                <a href="{{ route('admin.orders.index') }}" class="dash-hero__btn dash-hero__btn--gold">
                    <x-icon name="shopping-bag" class="w-4 h-4" /> View orders
                </a>
                @if ($isSuperAdmin)
                    <a href="{{ route('admin.revenue.index') }}" class="dash-hero__btn dash-hero__btn--ghost">
                        See revenue <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                @endif
            </x-slot:actions>
        @endif
    </x-dash-hero>

    @if ($overview)
        @php
            $money = fn ($n) => '₹'.number_format((float) $n);
            $ordersUrl = route('admin.orders.index');
            $pendingUrl = route('admin.orders.index', ['status' => 'pending']);
            $deliveredUrl = route('admin.orders.index', ['status' => 'delivered']);
            $revenueUrl = $isSuperAdmin ? route('admin.revenue.index') : $ordersUrl;
        @endphp

        <x-dash-heading title="Sales & Revenue" subtitle="Your business at a glance" icon="sparkles" />

        {{-- Action KPIs: each box links to its related page; colour signals the action/data. --}}
        <div class="d-grid grid-cols-1 grid-cols-sm-2 grid-cols-xl-4 gap-4 mb-6">
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
            subheading="The two streams that make up company income"
            :vip-hint="'Company share of '.$overview['vip']['count'].' plan'.($overview['vip']['count'] === 1 ? '' : 's').' sold'"
            :product-hint="'Company margin on '.$overview['products']['count'].' delivered order'.($overview['products']['count'] === 1 ? '' : 's')" />
    @endif

    @if ($withdrawals)
        <x-dash-heading title="Payouts" subtitle="Money waiting to go out" icon="rupee" tone="gold" />
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
        <x-dash-heading title="Network" subtitle="Your team & catalogue" icon="users" tone="slate" />
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
        <x-dash-heading title="Trends" subtitle="How the platform is growing" icon="sparkles" tone="emerald" />
        <div class="d-grid grid-cols-lg-3 gap-4 mb-4">
            <x-dash-card class="col-span-lg-2" title="Company Revenue" subtitle="Company share of activations, last 6 months" icon="rupee">
                <x-apex-chart id="chart-admin-revenue" type="area" :height="300"
                    :series="[['name' => 'Company Revenue', 'data' => $charts['revenue']['data']]]"
                    :categories="$charts['revenue']['categories']" :currency="true" />
            </x-dash-card>
            <x-dash-card title="Commission Split" subtitle="All-time payout distribution" icon="sparkles">
                <x-apex-chart id="chart-admin-split" type="donut" :height="300"
                    :series="[$charts['split']['partners'], $charts['split']['managers'], $charts['split']['company']]"
                    :labels="['Partners', 'Managers', 'Company']"
                    :colors="['#5fa97e', '#d4af37', '#2c704c']" :currency="true" />
            </x-dash-card>
        </div>

        <div class="d-grid grid-cols-lg-2 gap-4 mb-8">
            <x-dash-card title="New Users" subtitle="Sign-ups per month, last 6 months" icon="users">
                <x-apex-chart id="chart-admin-users" type="bar" :height="280"
                    :series="[['name' => 'New Users', 'data' => $charts['users']['data']]]"
                    :categories="$charts['users']['categories']" :colors="['#2c704c']" />
            </x-dash-card>
            <x-dash-card title="Leads" subtitle="Enquiries received per month, last 6 months" icon="inbox">
                <x-apex-chart id="chart-admin-leads" type="area" :height="280"
                    :series="[['name' => 'Leads', 'data' => $charts['leads']['data']]]"
                    :categories="$charts['leads']['categories']" :colors="['#d4af37']" />
            </x-dash-card>
        </div>
    @endif

    {{-- A Sub Admin's own network. The platform-wide figures above are not
         theirs to see, so these count only what they own. --}}
    @if ($network)
        <x-dash-heading title="My Network" subtitle="The Branch Managers you run" icon="users" tone="slate" />
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
        <x-dash-card title="Your Permission Matrix" subtitle="What your Super Admin has switched on for you" icon="lock">
            <div class="table-responsive">
                <table class="dash-table w-100 fs-sm">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Actions Granted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissionMatrix as $module => $actions)
                            <tr>
                                <td class="fw-medium text-capitalize">{{ str_replace('-', ' ', $module) }}</td>
                                <td>
                                    @forelse ($actions as $action)
                                        <span class="dash-pill">{{ $action }}</span>
                                    @empty
                                        <span class="text-body-tertiary">none</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-dash-card>
    @endif

    @unless ($stats || $permissionMatrix || $network)
        <div class="dash-empty">
            <span class="dash-empty__icon"><x-icon name="lock" class="w-6 h-6" /></span>
            <p class="mb-0">You don't have any modules assigned yet. Contact your Super Admin.</p>
        </div>
    @endunless
</x-layouts.app>
