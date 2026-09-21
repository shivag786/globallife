@props([
    'package',
    // Current usage, when the member already has content (renewal). Null on
    // creation, where there is nothing to compare against yet.
    'productsUsed' => null,
    'servicesUsed' => null,
    // Renders the whole card as a radio label for use inside a bigger form.
    'selectable' => false,
    'name' => 'vip_plan_id',
    'checked' => false,
    'currentLabel' => null,
])

@php
    $money = fn ($n) => '₹'.number_format((float) $n, 0);
    $popular = $package->isMostPopular();

    $productsFit = $productsUsed === null || $productsUsed <= $package->productLimit();
    $servicesFit = $servicesUsed === null || $servicesUsed <= $package->serviceLimit();
    $fits = $productsFit && $servicesFit;
    $hasUsage = $productsUsed !== null && $servicesUsed !== null;

    $frame = 'relative bg-white rounded-2xl flex flex-col premium-shadow transition h-full '
        .($popular ? 'border-2 border-gold-500' : 'border border-slate-200');
@endphp

<{{ $selectable ? 'label' : 'div' }} class="{{ $selectable ? 'block cursor-pointer h-full' : 'h-full' }}">
    @if ($selectable)
        <input type="radio" name="{{ $name }}" value="{{ $package->id }}" class="pkg-radio sr-only" @checked($checked)>
    @endif

    <div class="{{ $frame }} hover:-translate-y-1 {{ $selectable ? 'pkg-frame' : '' }}">
        @if ($popular)
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gold-500 text-brand-950 text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full whitespace-nowrap shadow">
                Most Popular
            </span>
        @endif

        {{-- Name + price --}}
        <div class="px-6 pt-7 pb-5 text-center border-b border-slate-100">
            <h4 class="font-display text-lg font-bold text-brand-900 uppercase tracking-wide">{{ $package->name }}</h4>
            @if ($currentLabel)
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $currentLabel }}</p>
            @endif
            <p class="mt-3">
                <span class="font-display text-4xl font-extrabold text-brand-800">{{ $money($package->renewal_price) }}</span>
            </p>
            <p class="inline-flex items-center gap-1.5 mt-2 bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1 rounded-full">
                <x-icon name="calendar" class="w-3.5 h-3.5" />
                {{ $package->validityLabel() }} validity
            </p>
        </div>

        {{-- What it buys --}}
        <div class="px-6 py-5 flex-1 space-y-3 text-sm">
            <div class="flex items-center gap-2.5">
                <x-icon name="{{ $productsFit ? 'check-circle' : 'x-mark' }}"
                        class="w-4 h-4 flex-shrink-0 {{ $productsFit ? 'text-brand-500' : 'text-red-500' }}" />
                <span class="text-slate-600">
                    <span class="font-bold text-brand-900">{{ $package->productLimit() }}</span> product catalogue
                </span>
            </div>
            <div class="flex items-center gap-2.5">
                <x-icon name="{{ $servicesFit ? 'check-circle' : 'x-mark' }}"
                        class="w-4 h-4 flex-shrink-0 {{ $servicesFit ? 'text-brand-500' : 'text-red-500' }}" />
                <span class="text-slate-600">
                    <span class="font-bold text-brand-900">{{ $package->serviceLimit() }}</span> services
                </span>
            </div>

            @if ($hasUsage && $fits)
                <p class="text-xs text-slate-400 pt-1 border-t border-slate-100 mt-3">
                    Room for {{ $package->productLimit() - $productsUsed }} more products and
                    {{ $package->serviceLimit() - $servicesUsed }} more services.
                </p>
            @elseif ($hasUsage)
                <p class="text-xs text-red-600 pt-2 border-t border-red-100 mt-3 flex items-start gap-1.5">
                    <x-icon name="x-mark" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" />
                    <span>Below what they already use. They keep everything, but can add no more.</span>
                </p>
            @endif
        </div>

        <div class="px-6 pb-6">
            @if ($selectable)
                    {{-- No JS: the radio's :checked state drives both labels (see app.css). --}}
                <span class="pkg-select block w-full text-center py-3 rounded-full font-semibold text-sm border border-slate-200 text-slate-500">
                    Select
                </span>
                <span class="pkg-selected hidden w-full text-center py-3 rounded-full font-semibold text-sm bg-brand-700 text-white">
                    Selected
                </span>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</{{ $selectable ? 'label' : 'div' }}>
