@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'help' => null,
    'as' => 'input',
])

{{-- mt-1 / shadow-sm are avoided below — Bootstrap defines both as utilities too,
     and its .bootstrap-scope-scoped rule would win on specificity. --}}
@php
    $errorKey = $name;
    $inputValue = old($errorKey, $value);
    $baseClasses = 'mt-[0.25rem] block w-full rounded-lg border-slate-300 shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-sm text-slate-800 '
        . 'transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 focus:outline-none '
        . 'placeholder:text-slate-400 disabled:bg-slate-100 disabled:text-slate-400';
    $errorClasses = $errors->has($errorKey) ? 'border-red-300 focus:border-red-500 focus:ring-red-500/30' : '';
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    @if ($as === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4"
                  {{ $attributes->merge(['class' => "$baseClasses $errorClasses"]) }}>{{ $inputValue }}</textarea>
    @elseif ($as === 'select')
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => "$baseClasses $errorClasses"]) }}>
            {{ $slot }}
        </select>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $inputValue }}"
               {{ $attributes->merge(['class' => "$baseClasses $errorClasses"]) }}>
    @endif

    @if ($help)
        <p class="mt-[0.25rem] text-xs text-slate-400">{{ $help }}</p>
    @endif

    @error($errorKey)
        <p class="mt-[0.25rem] text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
