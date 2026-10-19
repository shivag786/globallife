@props([
    'name',
    'label',
    'value' => null,
    'required' => false,
    'help' => null,
])

{{--
    A mobile number with a fixed +91 prefix and a live "7/10" digit counter.
    The digits themselves are handled by data-mobile-input (mobile-input.js):
    a paste or autofill keeps the last 10, typing stops at 10.
--}}
@php
    $inputValue = old($name, $value);
    $invalid = $errors->has($name);
@endphp

<div>
    <label for="{{ $name }}" class="form-label small fw-medium">
        {{ $label }}
        @unless ($required)
            <span class="text-secondary fw-normal">(optional)</span>
        @endunless
    </label>

    <div class="input-group member-mobile">
        <span class="input-group-text">+91</span>
        <input type="tel"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $inputValue }}"
               inputmode="numeric"
               autocomplete="{{ $name === 'mobile' ? 'tel-national' : 'off' }}"
               maxlength="10"
               placeholder="10 digits"
               data-mobile-input
               @if ($required) required @endif
               {{ $attributes->merge(['class' => 'form-control'.($invalid ? ' is-invalid' : '')]) }}>
        <span class="member-mobile-count" data-mobile-count aria-hidden="true">0/10</span>
    </div>

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
