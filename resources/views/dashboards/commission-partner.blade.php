<x-layouts.app title="Commission Partner Dashboard" heading="Commission Partner Dashboard">
    <div class="mb-6">
        <p class="text-slate-600">Welcome back, <strong>{{ $manager->name }}</strong>.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 mb-6">
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

    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-100 text-slate-500">
        Lead management, VIP enquiries, sales/revenue tracking, discount management, and customer
        management for your assigned cities ship in Phase 2.
    </div>
</x-layouts.app>
