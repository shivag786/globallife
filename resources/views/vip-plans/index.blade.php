<x-layouts.public title="VIP Plans" :metaDescription="'Compare Global Life VIP membership plans and choose the one that fits your business.'">
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl font-bold text-brand-900 mb-3">Compare VIP Plans</h1>
            <p class="text-slate-500">Choose the plan that fits your business.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($plans as $index => $plan)
                <div class="reveal" style="transition-delay: {{ $index * 0.1 }}s">
                    @include('partials.plan-card', ['plan' => $plan])
                </div>
            @endforeach
        </div>

        <p class="text-center text-slate-400 text-sm mt-10">
            VIP enquiry submission &amp; city-based routing to managers ships in Phase 2.
        </p>
    </div>
</x-layouts.public>
