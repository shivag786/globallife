@php $plans = app(\App\Repositories\VipPlanRepository::class)->activeOrdered(); @endphp
<div class="mw-2xl mx-auto px-6 py-16">
    <div class="text-center mb-10 reveal">
        <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
        @if ($section->subtitle)
            <p class="text-secondary">{{ $section->subtitle }}</p>
        @endif
    </div>
    <div class="reveal bg-body border rounded-4 p-8 premium-shadow">
        @include('partials.enquiry-form', ['source' => 'homepage', 'plans' => $plans])
    </div>
</div>
