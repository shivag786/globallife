<x-layouts.app title="Commission Partner Dashboard" heading="Commission Partner Dashboard">
    {{-- A partner whose Branch Manager has not set a rate yet would otherwise
         get a blank tile and a bare "%" below; say so plainly instead. --}}
    @php $rateSet = $manager->commission_percentage !== null; @endphp

    <x-dash-hero :name="$manager->name" eyebrow="Commission Partner" subtitle="Here's what your members have earned you.">
        <x-slot:meta>
            <span class="dash-hero__chip"><x-icon name="tag" class="w-3.5 h-3.5" /> {{ $rateSet ? $manager->commission_percentage.'% commission' : 'Commission not set yet' }}</span>
            <span class="dash-hero__chip"><x-icon name="users" class="w-3.5 h-3.5" /> {{ number_format($stats['vip_members']) }} VIP members</span>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('manager.vip-members.index') }}" class="dash-hero__btn dash-hero__btn--gold">
                <x-icon name="users" class="w-4 h-4" /> Manage members
            </a>
            <a href="{{ route('manager.revenue.index') }}" class="dash-hero__btn dash-hero__btn--ghost">
                View revenue <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </x-slot:actions>
    </x-dash-hero>

    <x-dash-heading title="At a Glance" subtitle="Your members and earnings in numbers" icon="sparkles" />
    <div class="d-grid grid-cols-2 grid-cols-md-4 gap-4 mb-8">
        <x-stat-tile label="Your Commission" :value="$rateSet ? $manager->commission_percentage : null" suffix="%"
            icon="tag" color="brand"
            :hint="$rateSet ? 'Your share of each activation' : 'Not set yet — ask your Branch Manager'" />
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

    <div class="d-grid grid-cols-lg-3 gap-4">
        <x-dash-card class="col-span-lg-2" title="Your Earnings" subtitle="Commission earned per month, last 6 months" icon="rupee">
            <x-apex-chart id="chart-partner-earnings" type="area" :height="300"
                :series="[['name' => 'Earnings', 'data' => $earningsChart['data']]]"
                :categories="$earningsChart['categories']" :currency="true" />
        </x-dash-card>

        <x-dash-card title="Your Assigned Cities" subtitle="Where you add VIP members" icon="map-pin">
            @forelse ($manager->cities as $city)
                @if ($loop->first)<div class="dash-places dash-places--stack">@endif
                <div class="dash-place">
                    <span class="dash-place__icon"><x-icon name="map-pin" class="w-4 h-4" /></span>
                    <span class="min-w-0">
                        <span class="dash-place__name">{{ $city->name }}</span>
                        <span class="dash-place__state">{{ $city->state }}</span>
                    </span>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <p class="text-secondary fs-sm mb-0">No cities assigned yet. Contact your Branch Manager.</p>
            @endforelse
            <div class="dash-note mt-4">
                <x-icon name="tag" class="w-4 h-4 flex-shrink-0" />
                <span>
                    Commission: <strong class="text-brand">{{ $rateSet ? $manager->commission_percentage.'%' : 'not set yet' }}</strong>
                    @if ($manager->creator)
                        &mdash; set by {{ $manager->creator->name }}
                    @endif
                </span>
            </div>
        </x-dash-card>
    </div>
</x-layouts.app>
