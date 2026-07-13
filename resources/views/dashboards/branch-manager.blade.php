<x-layouts.app title="Branch Manager Dashboard" heading="Branch Manager Dashboard">
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong>{{ $manager->name }}</strong>.</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <h2 class="font-semibold mb-3">Your Branches (Cities)</h2>
            @forelse ($manager->branchCities as $city)
                <div class="flex items-center justify-between border-t border-slate-100 py-2 first:border-t-0">
                    <span>{{ $city->name }}, {{ $city->state }}</span>
                </div>
            @empty
                <p class="text-slate-400 text-sm">No cities assigned yet. Contact your Super Admin.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100">
            <h2 class="font-semibold mb-3">Commission</h2>
            <p class="text-3xl font-bold text-brand-700">{{ $manager->commission_percentage }}%</p>
            <p class="text-sm text-slate-500 mt-1">Your cap — you can grant Commission Partners up to this rate.</p>
            <p class="text-sm text-slate-500 mt-3">
                <a href="{{ route('branch.commission-partners.index') }}" class="text-brand-700 hover:underline">
                    {{ $partnerCount }} Commission Partner{{ $partnerCount === 1 ? '' : 's' }}
                </a>
            </p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
        VIP enquiries, sales/revenue tracking, discount management, and customer management for your
        branches ship in Phase 2.
    </div>
</x-layouts.app>
