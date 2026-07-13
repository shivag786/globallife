@php $highlighted = in_array($plan->slug, ['platinum-vip', 'gold-vip'], true); @endphp
<div class="relative border {{ $highlighted ? 'border-brand-600 ring-2 ring-brand-600' : 'border-slate-200' }} bg-white rounded-2xl p-6 flex flex-col premium-shadow hover:-translate-y-1 transition">
    @if ($highlighted)
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gold-500 text-white text-xs font-semibold px-3 py-1 rounded-full">Popular</span>
    @endif
    <h3 class="font-display text-xl font-bold text-brand-900 mb-1">{{ $plan->name }}</h3>
    <p class="text-3xl font-extrabold text-brand-800 mb-1">₹{{ number_format($plan->monthly_price, 0) }}<span class="text-sm font-normal text-slate-400">/mo</span></p>
    <p class="text-sm text-slate-400 mb-4">or ₹{{ number_format($plan->yearly_price, 0) }}/yr &middot; ₹{{ number_format($plan->joining_price, 0) }} joining</p>

    <ul class="space-y-2 text-sm flex-1 mb-6">
        @foreach ($plan->features ?? [] as $feature)
            <li class="flex items-start gap-2">
                <x-icon name="check-circle" class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" /> {{ $feature }}
            </li>
        @endforeach
    </ul>

    <a href="{{ route('contact', ['plan' => $plan->slug]) }}" class="block text-center bg-brand-700 text-white py-2.5 rounded-full font-medium hover:bg-brand-800 transition">
        Enquire Now
    </a>
</div>
