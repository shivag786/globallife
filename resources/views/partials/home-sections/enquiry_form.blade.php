@php $plans = app(\App\Repositories\VipPlanRepository::class)->activeOrdered(); @endphp
<div class="max-w-2xl mx-auto px-6 py-16">
    <div class="text-center mb-10 reveal">
        <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
        @if ($section->subtitle)
            <p class="text-slate-500">{{ $section->subtitle }}</p>
        @endif
    </div>
    <div class="reveal bg-white border border-slate-100 rounded-2xl p-8 premium-shadow">
        @include('partials.enquiry-form', ['source' => 'homepage', 'plans' => $plans])
    </div>
</div>
