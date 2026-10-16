<x-layouts.public title="VIP Plans" :metaDescription="'Compare Global Life VIP membership plans and choose the one that fits your business.'">
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display fs-4xl fw-bold text-brand-emphasis mb-3">Compare VIP Plans</h1>
            <p class="text-secondary">Choose the plan that fits your business.</p>
        </div>

        <div class="d-grid grid-cols-1 grid-cols-md-2 grid-cols-lg-4 gap-6">
            @foreach ($plans as $index => $plan)
                <div class="reveal" style="transition-delay: {{ $index * 0.1 }}s">
                    @include('partials.plan-card', ['plan' => $plan])
                </div>
            @endforeach
        </div>

        <p class="text-center text-secondary fs-sm mt-10">
            VIP enquiry submission &amp; city-based routing to managers ships in Phase 2.
        </p>
    </div>
</x-layouts.public>
