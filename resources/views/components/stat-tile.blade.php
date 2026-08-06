@props([
    'label',
    'value' => null,
    'icon' => null,
    'color' => 'brand',
    'href' => null,
    'hint' => null,
    'cta' => null,
    'money' => false,
    'suffix' => '',
])

@php
    // Literal class strings per colour so Tailwind can see (and generate) them at
    // build time. Each tile is a soft gradient with a thin, colour-matched border.
    $palettes = [
        'brand'   => ['border' => 'border-brand-200/70',   'hover' => 'hover:border-brand-300',   'from' => 'from-brand-50',   'chip' => 'bg-brand-100 text-brand-700',     'label' => 'text-brand-600',   'cta' => 'text-brand-700'],
        'emerald' => ['border' => 'border-emerald-200/80', 'hover' => 'hover:border-emerald-300', 'from' => 'from-emerald-50', 'chip' => 'bg-emerald-100 text-emerald-600', 'label' => 'text-emerald-600', 'cta' => 'text-emerald-600'],
        'teal'    => ['border' => 'border-teal-200/80',    'hover' => 'hover:border-teal-300',    'from' => 'from-teal-50',    'chip' => 'bg-teal-100 text-teal-600',       'label' => 'text-teal-600',    'cta' => 'text-teal-600'],
        'sky'     => ['border' => 'border-sky-200/80',     'hover' => 'hover:border-sky-300',     'from' => 'from-sky-50',     'chip' => 'bg-sky-100 text-sky-600',         'label' => 'text-sky-600',     'cta' => 'text-sky-600'],
        'indigo'  => ['border' => 'border-indigo-200/70',  'hover' => 'hover:border-indigo-300',  'from' => 'from-indigo-50',  'chip' => 'bg-indigo-100 text-indigo-600',   'label' => 'text-indigo-500',  'cta' => 'text-indigo-600'],
        'violet'  => ['border' => 'border-violet-200/80',  'hover' => 'hover:border-violet-300',  'from' => 'from-violet-50',  'chip' => 'bg-violet-100 text-violet-600',   'label' => 'text-violet-600',  'cta' => 'text-violet-600'],
        'amber'   => ['border' => 'border-amber-200/80',   'hover' => 'hover:border-amber-300',   'from' => 'from-amber-50',   'chip' => 'bg-amber-100 text-amber-600',     'label' => 'text-amber-600',   'cta' => 'text-amber-600'],
        'rose'    => ['border' => 'border-rose-200/80',    'hover' => 'hover:border-rose-300',    'from' => 'from-rose-50',    'chip' => 'bg-rose-100 text-rose-600',       'label' => 'text-rose-600',    'cta' => 'text-rose-600'],
        'cyan'    => ['border' => 'border-cyan-200/80',    'hover' => 'hover:border-cyan-300',    'from' => 'from-cyan-50',    'chip' => 'bg-cyan-100 text-cyan-600',       'label' => 'text-cyan-600',    'cta' => 'text-cyan-600'],
        'slate'   => ['border' => 'border-slate-200',      'hover' => 'hover:border-slate-300',   'from' => 'from-slate-50',   'chip' => 'bg-slate-100 text-slate-600',     'label' => 'text-slate-500',   'cta' => 'text-slate-600'],
    ];
    $p = $palettes[$color] ?? $palettes['brand'];
    $display = $money ? '₹'.number_format((float) $value) : null;
    $interactive = $href ? "{$p['hover']} hover:-translate-y-0.5 hover:shadow-lg" : '';
@endphp

@if ($href)
<a href="{{ $href }}" {{ $attributes->class(["group relative block overflow-hidden rounded-2xl border {$p['border']} bg-gradient-to-br {$p['from']} via-white to-white p-5 transition {$interactive}"]) }}>
@else
<div {{ $attributes->class(["relative overflow-hidden rounded-2xl border {$p['border']} bg-gradient-to-br {$p['from']} via-white to-white p-5"]) }}>
@endif
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide {{ $p['label'] }}">{{ $label }}</p>
            @if ($money)
                <p class="mt-2 text-2xl sm:text-3xl font-bold text-slate-800 truncate">{{ $display }}</p>
            @elseif (! is_null($value))
                <p class="mt-2 text-3xl font-bold text-slate-800" data-countup="{{ $value }}{{ $suffix }}">0</p>
            @endif
            @isset($extra){{ $extra }}@endisset
            @if ($hint)
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $p['chip'] }}">
                <x-icon :name="$icon" class="w-6 h-6" />
            </span>
        @endif
    </div>
    @if ($href && $cta)
        <span class="mt-3 inline-flex items-center gap-1 text-xs font-medium {{ $p['cta'] }} opacity-0 group-hover:opacity-100 transition">
            {{ $cta }} <x-icon name="arrow-right" class="w-3.5 h-3.5" />
        </span>
    @endif
@if ($href)</a>@else</div>@endif
