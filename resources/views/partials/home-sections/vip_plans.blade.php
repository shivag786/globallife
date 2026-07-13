@php $plans = app(\App\Repositories\VipPlanRepository::class)->activeOrdered(); @endphp
@if ($plans->isNotEmpty())
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($plans as $index => $plan)
                <div class="reveal" style="transition-delay: {{ $index * 0.1 }}s">
                    @include('partials.plan-card', ['plan' => $plan])
                </div>
            @endforeach
        </div>
    </div>
@endif
