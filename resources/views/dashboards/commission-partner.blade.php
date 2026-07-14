<x-layouts.app title="Commission Partner Dashboard" heading="Commission Partner Dashboard">
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong>{{ $manager->name }}</strong>.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase tracking-wide text-slate-400">Your Commission</p>
            <p class="text-2xl font-bold text-brand-700">{{ $manager->commission_percentage }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase tracking-wide text-slate-400">VIP Members</p>
            <p class="text-2xl font-bold text-slate-800" data-countup="{{ $stats['vip_members'] }}">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase tracking-wide text-slate-400">Activations</p>
            <p class="text-2xl font-bold text-slate-800" data-countup="{{ $stats['activations'] }}">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs uppercase tracking-wide text-slate-400">Total Earned</p>
            <p class="text-2xl font-bold text-slate-800">₹{{ number_format($stats['earned'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100 mb-6">
        <h2 class="font-semibold text-slate-800 mb-1">Your Earnings</h2>
        <p class="text-xs text-slate-400 mb-3">Commission earned per month, last 6 months</p>
        <x-apex-chart id="chart-partner-earnings" type="area" :height="300"
            :series="[['name' => 'Earnings', 'data' => $earningsChart['data']]]"
            :categories="$earningsChart['categories']" :currency="true" />
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-100">
        <h2 class="font-semibold mb-3">Your Assigned Cities</h2>
        @forelse ($manager->cities as $city)
            <div class="flex items-center justify-between border-t border-slate-100 py-2 first:border-t-0">
                <span>{{ $city->name }}, {{ $city->state }}</span>
            </div>
        @empty
            <p class="text-slate-400 text-sm">No cities assigned yet. Contact your Branch Manager.</p>
        @endforelse
        <div class="border-t border-slate-100 pt-3 mt-3 text-sm text-slate-500">
            Commission: <strong class="text-brand-700">{{ $manager->commission_percentage }}%</strong>
            @if ($manager->creator)
                &mdash; set by {{ $manager->creator->name }}
            @endif
        </div>
    </div>
</x-layouts.app>
