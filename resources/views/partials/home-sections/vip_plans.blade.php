@php $plans = app(\App\Repositories\VipPlanRepository::class)->activeOrdered(); @endphp
@if ($plans->isNotEmpty())
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="d-grid grid-cols-1 grid-cols-md-2 grid-cols-lg-4 gap-6">
            @foreach ($plans as $index => $plan)
                <div class="reveal" style="transition-delay: {{ $index * 0.1 }}s">
                    @include('partials.plan-card', ['plan' => $plan])
                </div>
            @endforeach
        </div>
    </div>
@endif
