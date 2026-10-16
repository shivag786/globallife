@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'help' => null,
    'as' => 'input',
])

@php
    $errorKey = $name;
    $inputValue = old($errorKey, $value);
    // Bootstrap owns the look now: .form-control / .form-select, plus
    // .is-invalid which it styles and which pairs with .invalid-feedback
    // below. The input border colour is one SCSS dial ($input-border-color).
    $control = $as === 'select' ? 'form-select' : 'form-control';
    $invalid = $errors->has($errorKey) ? ' is-invalid' : '';
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label small fw-medium">{{ $label }}</label>
    @endif

    @if ($as === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4"
                  {{ $attributes->merge(['class' => $control.$invalid]) }}>{{ $inputValue }}</textarea>
    @elseif ($as === 'select')
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => $control.$invalid]) }}>
            {{ $slot }}
        </select>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $inputValue }}"
               {{ $attributes->merge(['class' => $control.$invalid]) }}>
    @endif

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
