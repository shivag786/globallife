@props([
    'vip',
    'product',
    'total',
    'productPending' => 0,
    'vipHref' => null,
    'productHref' => null,
    'vipLabel' => 'Revenue from VIP Activations',
    'vipHint' => null,
    'productHint' => 'From product sales',
    'totalLabel' => 'Total Revenue',
    'heading' => 'Revenue',
    'subheading' => 'where your earnings come from',
])

@php $money = fn ($n) => '₹'.number_format((float) $n); @endphp

<div class="mb-3 d-flex align-items-center gap-2">
    <span class="d-flex h-7 w-7 align-items-center justify-content-center rounded-2 bg-success-subtle text-success">
        <x-icon name="rupee" class="w-4 h-4" />
    </span>
    <h2 class="fw-semibold text-body">{{ $heading }}</h2>
    <span class="fs-xs text-secondary">— {{ $subheading }}</span>
</div>

<div class="d-grid grid-template-lg gap-4 mb-8" style="--cols-lg: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 0.9fr)">
    {{-- From VIP activations --}}
    <{{ $vipHref ? 'a' : 'div' }} @if ($vipHref) href="{{ $vipHref }}" @endif
        class="d-block rounded-4 border border-brand-subtle p-5 text-reset text-decoration-none {{ $vipHref ? 'stat-tile--lift' : '' }}"
        style="background: linear-gradient(to bottom right, var(--color-brand-50), var(--bs-body-bg), var(--bs-body-bg))">
        <div class="d-flex align-items-center justify-content-between">
            <p class="fs-sm fw-semibold text-body-secondary">{{ $vipLabel }}</p>
            <span class="d-flex h-9 w-9 align-items-center justify-content-center rounded-2 bg-brand-subtle text-brand">
                <x-icon name="sparkles" class="w-5 h-5" />
            </span>
        </div>
        <p class="mt-3 fs-2xl fw-bold text-brand-emphasis">{{ $money($vip) }}</p>
        @if ($vipHint)<p class="mt-1 fs-xs text-secondary">{{ $vipHint }}</p>@endif
    </{{ $vipHref ? 'a' : 'div' }}>

    {{-- From product sales --}}
    <{{ $productHref ? 'a' : 'div' }} @if ($productHref) href="{{ $productHref }}" @endif
        class="d-block rounded-4 border border-success-subtle p-5 text-reset text-decoration-none {{ $productHref ? 'stat-tile--lift' : '' }}"
        style="background: linear-gradient(to bottom right, color-mix(in srgb, #14b8a6 12%, var(--bs-body-bg)), var(--bs-body-bg), var(--bs-body-bg))">
        <div class="d-flex align-items-center justify-content-between">
            <p class="fs-sm fw-semibold text-body-secondary">Revenue from Products</p>
            <span class="d-flex h-9 w-9 align-items-center justify-content-center rounded-2 bg-success-subtle text-success">
                <x-icon name="tag" class="w-5 h-5" />
            </span>
        </div>
        <p class="mt-3 fs-2xl fw-bold text-success">{{ $money($product) }}</p>
        <p class="mt-1 fs-xs text-secondary">
            {{ $productHint }}@if ($productPending > 0) · {{ $money($productPending) }} pending @endif
        </p>
    </{{ $productHref ? 'a' : 'div' }}>

    {{-- Combined total --}}
    <div class="rounded-4 border border-success-subtle p-5 text-white shadow-sm" style="background: linear-gradient(to bottom right, #059669, var(--color-brand-700))">
        <div class="d-flex align-items-center justify-content-between">
            <p class="fs-sm fw-semibold">{{ $totalLabel }}</p>
            <span class="d-flex h-9 w-9 align-items-center justify-content-center rounded-2 bg-white bg-opacity-10 text-white">
                <x-icon name="rupee" class="w-5 h-5" />
            </span>
        </div>
        <p class="mt-3 fs-3xl fw-bold">{{ $money($total) }}</p>
        <p class="mt-1 fs-xs">VIP + products combined</p>
    </div>
</div>
