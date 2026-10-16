@php $highlighted = in_array($plan->slug, ['platinum-vip', 'gold-vip'], true); @endphp
<div class="position-relative border {{ $highlighted ? 'border-brand' : '' }} bg-body rounded-4 p-6 d-flex flex-column premium-shadow">
    @if ($highlighted)
        <span class="position-absolute top-n3 start-50 bg-gold text-white fs-xs fw-semibold px-3 py-1 rounded-pill">Popular</span>
    @endif
    <h3 class="font-display fs-xl fw-bold text-brand-emphasis mb-1">{{ $plan->name }}</h3>
    <p class="fs-3xl fw-bolder text-brand-emphasis mb-1">₹{{ number_format($plan->monthly_price, 0) }}<span class="fs-sm fw-normal text-secondary">/mo</span></p>
    <p class="fs-sm text-secondary mb-4">or ₹{{ number_format($plan->yearly_price, 0) }}/yr &middot; ₹{{ number_format($plan->joining_price, 0) }} joining</p>

    <ul class="d-flex flex-column gap-2 fs-sm flex-grow-1 mb-6">
        @foreach ($plan->features ?? [] as $feature)
            <li class="d-flex align-items-start gap-2">
                <x-icon name="check-circle" class="w-4 h-4 text-brand flex-shrink-0 mt-1" /> {{ $feature }}
            </li>
        @endforeach
    </ul>

    <a href="{{ route('contact', ['plan' => $plan->slug]) }}" class="btn btn-brand rounded-pill d-block text-center">
        Enquire Now
    </a>
</div>
