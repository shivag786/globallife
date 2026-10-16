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

    $frame = 'position-relative bg-body rounded-4 d-flex flex-column premium-shadow h-100'
        .($popular ? 'border-2 border-gold' : 'border');
@endphp

<{{ $selectable ? 'label' : 'div' }} class="{{ $selectable ? 'd-block h-100' : 'h-100' }}" @if ($selectable) style="cursor: pointer" @endif>
    @if ($selectable)
        <input type="radio" name="{{ $name }}" value="{{ $package->id }}" class="pkg-radio visually-hidden" @checked($checked)>
    @endif

    <div class="{{ $frame }} {{ $selectable ? 'pkg-frame' : '' }}">
        @if ($popular)
            <span class="position-absolute top-n3 start-50 bg-gold text-brand-emphasis fw-bold text-uppercase tracking-wider px-3 py-1 rounded-pill text-nowrap shadow-sm">
                Most Popular
            </span>
        @endif

        {{-- Name + price --}}
        <div class="px-6 pt-7 pb-5 text-center border-bottom">
            <h4 class="font-display fs-lg fw-bold text-brand-emphasis text-uppercase tracking-wide">{{ $package->name }}</h4>
            @if ($currentLabel)
                <p class="text-secondary mt-1">{{ $currentLabel }}</p>
            @endif
            <p class="mt-3">
                <span class="font-display fs-4xl fw-bolder text-brand-emphasis">{{ $money($package->renewal_price) }}</span>
            </p>
            <p class="d-inline-flex align-items-center gap-2 mt-2 bg-brand-subtle text-brand fs-xs fw-semibold px-3 py-1 rounded-pill">
                <x-icon name="calendar" class="w-3.5 h-3.5" />
                {{ $package->validityLabel() }} validity
            </p>
        </div>

        {{-- What it buys --}}
        <div class="px-6 py-5 flex-grow-1 d-flex flex-column gap-3 fs-sm">
            <div class="d-flex align-items-center gap-2">
                <x-icon name="{{ $productsFit ? 'check-circle' : 'x-mark' }}"
                        class="w-4 h-4 flex-shrink-0 {{ $productsFit ? 'text-brand' : 'text-danger' }}" />
                <span class="text-body-secondary">
                    <span class="fw-bold text-brand-emphasis">{{ $package->productLimit() }}</span> product catalogue
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-icon name="{{ $servicesFit ? 'check-circle' : 'x-mark' }}"
                        class="w-4 h-4 flex-shrink-0 {{ $servicesFit ? 'text-brand' : 'text-danger' }}" />
                <span class="text-body-secondary">
                    <span class="fw-bold text-brand-emphasis">{{ $package->serviceLimit() }}</span> services
                </span>
            </div>

            @if ($hasUsage && $fits)
                <p class="fs-xs text-secondary pt-1 border-top mt-3">
                    Room for {{ $package->productLimit() - $productsUsed }} more products and
                    {{ $package->serviceLimit() - $servicesUsed }} more services.
                </p>
            @elseif ($hasUsage)
                <p class="fs-xs text-danger pt-2 border-top border-danger-subtle mt-3 d-flex align-items-start gap-2">
                    <x-icon name="x-mark" class="w-3.5 h-3.5 flex-shrink-0 mt-1" />
                    <span>Below what they already use. They keep everything, but can add no more.</span>
                </p>
            @endif
        </div>

        <div class="px-6 pb-6">
            @if ($selectable)
                    {{-- No JS: the radio's :checked state drives both labels (see app.scss). --}}
                <span class="pkg-select d-block w-100 text-center py-3 rounded-pill fw-semibold fs-sm border text-secondary">
                    Select
                </span>
                <span class="pkg-selected d-none w-100 text-center py-3 rounded-pill fw-semibold fs-sm bg-brand text-white">
                    Selected
                </span>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</{{ $selectable ? 'label' : 'div' }}>
