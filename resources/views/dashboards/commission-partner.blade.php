<x-layouts.app title="Commission Partner Dashboard" heading="Commission Partner Dashboard">
    <div class="mb-6">
        <p class="text-body-secondary">Welcome back, <strong>{{ $manager->name }}</strong>.</p>
    </div>

    <div class="d-grid grid-cols-2 grid-cols-md-4 gap-4 mb-6">
        <x-stat-tile label="Your Commission" :value="$manager->commission_percentage" suffix="%"
            icon="tag" color="brand" hint="Your share of each activation" />
        <x-stat-tile label="VIP Members" :value="$stats['vip_members']" icon="users" color="indigo"
            :href="route('manager.vip-members.index')" cta="Manage members" />
        <x-stat-tile label="Activations" :value="$stats['activations']" icon="sparkles" color="violet"
            :href="route('manager.revenue.index')" cta="View revenue" />
        <x-stat-tile label="Total Earned" :value="$stats['earned']" money icon="rupee" color="emerald"
            :href="route('manager.revenue.index')" cta="See breakdown" />
    </div>

    <x-revenue-flow
        :vip="$revenue['vip']" :product="$revenue['product']" :total="$revenue['total']"
        :product-pending="$revenue['product_pending']"
        :vip-href="route('manager.revenue.index')" :product-href="route('wallet.index')"
        vip-hint="Your share of VIP-plan activations"
        product-hint="Commission on product sales" />

    <div class="bg-body rounded-3 shadow-sm p-5 border mb-6">
        <h2 class="fw-semibold text-body mb-1">Your Earnings</h2>
        <p class="fs-xs text-secondary mb-3">Commission earned per month, last 6 months</p>
        <x-apex-chart id="chart-partner-earnings" type="area" :height="300"
            :series="[['name' => 'Earnings', 'data' => $earningsChart['data']]]"
            :categories="$earningsChart['categories']" :currency="true" />
    </div>

    <div class="bg-body rounded-3 shadow-sm p-5 border">
        <h2 class="fw-semibold mb-3">Your Assigned Cities</h2>
        @forelse ($manager->cities as $city)
            <div class="d-flex align-items-center justify-content-between border-top py-2">
                <span>{{ $city->name }}, {{ $city->state }}</span>
            </div>
        @empty
            <p class="text-secondary fs-sm">No cities assigned yet. Contact your Branch Manager.</p>
        @endforelse
        <div class="border-top pt-3 mt-3 fs-sm text-secondary">
            Commission: <strong class="text-brand">{{ $manager->commission_percentage }}%</strong>
            @if ($manager->creator)
                &mdash; set by {{ $manager->creator->name }}
            @endif
        </div>
    </div>
</x-layouts.app>
