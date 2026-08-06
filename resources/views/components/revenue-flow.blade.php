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

<div class="mb-3 flex items-center gap-2">
    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
        <x-icon name="rupee" class="w-4 h-4" />
    </span>
    <h2 class="font-semibold text-slate-800">{{ $heading }}</h2>
    <span class="text-xs text-slate-400">— {{ $subheading }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr_0.9fr] gap-4 mb-8">
    {{-- From VIP activations --}}
    <{{ $vipHref ? 'a' : 'div' }} @if ($vipHref) href="{{ $vipHref }}" @endif
        class="group rounded-2xl border border-brand-200/70 bg-gradient-to-br from-brand-50 via-white to-white p-5 transition {{ $vipHref ? 'hover:shadow-lg hover:border-brand-300' : '' }}">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700">{{ $vipLabel }}</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-100 text-brand-700">
                <x-icon name="sparkles" class="w-5 h-5" />
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold text-brand-800">{{ $money($vip) }}</p>
        @if ($vipHint)<p class="mt-1 text-xs text-slate-400">{{ $vipHint }}</p>@endif
    </{{ $vipHref ? 'a' : 'div' }}>

    {{-- From product sales --}}
    <{{ $productHref ? 'a' : 'div' }} @if ($productHref) href="{{ $productHref }}" @endif
        class="group rounded-2xl border border-teal-200/70 bg-gradient-to-br from-teal-50 via-white to-white p-5 transition {{ $productHref ? 'hover:shadow-lg hover:border-teal-300' : '' }}">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700">Revenue from Products</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 text-teal-700">
                <x-icon name="tag" class="w-5 h-5" />
            </span>
        </div>
        <p class="mt-3 text-2xl font-bold text-teal-800">{{ $money($product) }}</p>
        <p class="mt-1 text-xs text-slate-400">
            {{ $productHint }}@if ($productPending > 0) · {{ $money($productPending) }} pending @endif
        </p>
    </{{ $productHref ? 'a' : 'div' }}>

    {{-- Combined total --}}
    <div class="rounded-2xl border border-emerald-300 bg-gradient-to-br from-emerald-600 to-brand-700 p-5 text-white shadow-md">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-emerald-50/90">{{ $totalLabel }}</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-white">
                <x-icon name="rupee" class="w-5 h-5" />
            </span>
        </div>
        <p class="mt-3 text-3xl font-bold">{{ $money($total) }}</p>
        <p class="mt-1 text-xs text-emerald-50/80">VIP + products combined</p>
    </div>
</div>
