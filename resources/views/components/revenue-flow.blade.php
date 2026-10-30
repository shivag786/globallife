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

<x-dash-heading :title="$heading" :subtitle="$subheading" icon="rupee" tone="emerald" />

{{-- VIP + products = total, read left to right; the + and = sit in the gaps on wide screens. --}}
<div class="flow-grid mb-8">
    {{-- From VIP activations --}}
    <{{ $vipHref ? 'a' : 'div' }} @if ($vipHref) href="{{ $vipHref }}" @endif
        class="flow-card flow-card--vip {{ $vipHref ? 'flow-card--link' : '' }}">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="flow-card__label">{{ $vipLabel }}</p>
            <span class="flow-card__chip"><x-icon name="sparkles" class="w-5 h-5" /></span>
        </div>
        <p class="flow-card__value">{{ $money($vip) }}</p>
        @if ($vipHint)<p class="flow-card__hint">{{ $vipHint }}</p>@endif
    </{{ $vipHref ? 'a' : 'div' }}>

    {{-- From product sales --}}
    <{{ $productHref ? 'a' : 'div' }} @if ($productHref) href="{{ $productHref }}" @endif
        class="flow-card flow-card--product {{ $productHref ? 'flow-card--link' : '' }}">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="flow-card__label">Revenue from Products</p>
            <span class="flow-card__chip"><x-icon name="tag" class="w-5 h-5" /></span>
        </div>
        <p class="flow-card__value">{{ $money($product) }}</p>
        <p class="flow-card__hint">
            {{ $productHint }}@if ($productPending > 0) · <span class="flow-card__pending">{{ $money($productPending) }} pending</span> @endif
        </p>
    </{{ $productHref ? 'a' : 'div' }}>

    {{-- Combined total --}}
    <div class="flow-card flow-card--total">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <p class="flow-card__label">{{ $totalLabel }}</p>
            <span class="flow-card__chip"><x-icon name="rupee" class="w-5 h-5" /></span>
        </div>
        <p class="flow-card__value">{{ $money($total) }}</p>
        <p class="flow-card__hint">VIP + products combined</p>
    </div>
</div>
