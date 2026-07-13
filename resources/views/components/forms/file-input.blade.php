@props([
    'name',
    'label' => null,
    'current' => null,
    'help' => null,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    <div class="mt-[0.25rem] flex items-center gap-[1rem]">
        @if ($current)
            <img src="{{ asset('storage/' . $current) }}" alt="{{ $label }}"
                 class="h-14 w-14 rounded-lg border border-slate-200 object-contain bg-white p-[0.25rem]">
        @else
            <span class="flex h-14 w-14 items-center justify-center rounded-lg border border-dashed border-slate-300 text-[10px] text-slate-400">
                No file
            </span>
        @endif

        <input id="{{ $name }}" type="file" name="{{ $name }}" accept="image/*"
               {{ $attributes->merge(['class' => 'block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100']) }}>
    </div>

    @if ($help)
        <p class="mt-[0.25rem] text-xs text-slate-400">{{ $help }}</p>
    @endif

    @error($name)
        <p class="mt-[0.25rem] text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
