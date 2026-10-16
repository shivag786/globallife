@props([
    'name',
    'label' => null,
    'current' => null,
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label small fw-medium">{{ $label }}</label>
    @endif

    <div class="d-flex align-items-center gap-3">
        @if ($current)
            <img src="{{ asset('storage/' . $current) }}" alt="{{ $label }}"
                 class="rounded border bg-body object-fit-contain p-1" width="56" height="56">
        @else
            <span class="d-flex align-items-center justify-content-center rounded border border-dashed text-secondary"
                  style="width: 3.5rem; height: 3.5rem; font-size: 0.625rem">
                No file
            </span>
        @endif

        <input id="{{ $name }}" type="file" name="{{ $name }}" accept="image/*"
               {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}>
    </div>

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
