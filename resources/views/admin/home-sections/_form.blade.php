@csrf
@isset($section) @method('PUT') @endisset

@php
    $itemsText = '';
    if (isset($section) && $section->items) {
        $keysByType = ['stats' => ['label', 'value'], 'team' => ['name', 'role']];
        $keys = $keysByType[$section->type] ?? ['title', 'description'];
        $itemsText = collect($section->items)
            ->map(fn ($item) => ($item[$keys[0]] ?? '').' | '.($item[$keys[1]] ?? ''))
            ->implode("\n");
    }
@endphp

<div class="space-y-4 max-w-2xl">
    <div>
        <label for="type" class="block text-sm font-medium text-slate-700">Section Type</label>
        <select id="type" name="type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (\App\Models\HomeSection::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $section->type ?? 'hero') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Title / Heading <span class="text-slate-400">(founder quote: founder's name)</span></label>
        <input id="title" type="text" name="title" value="{{ old('title', $section->title ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="subtitle" class="block text-sm font-medium text-slate-700">Subtitle <span class="text-slate-400">(hero, cta, founder quote: role/title)</span></label>
        <input id="subtitle" type="text" name="subtitle" value="{{ old('subtitle', $section->subtitle ?? '') }}"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="editor-content" class="block text-sm font-medium text-slate-700 mb-1">Body Content <span class="text-slate-400">(about, founder quote, business opportunity)</span></label>
        <x-ckeditor name="content" :value="$section->content ?? ''" :rows="6" />
    </div>

    <div>
        <label for="items" class="block text-sm font-medium text-slate-700">
            Items <span class="text-slate-400">(features/business opportunity/process steps: "Title | Description" per line &middot; stats: "Label | Value" per line &middot; team: "Name | Role" per line &middot; hero: trust badge label per line, optional)</span>
        </label>
        <textarea id="items" name="items" rows="5"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('items', $itemsText) }}</textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="cta_label" class="block text-sm font-medium text-slate-700">CTA Button Label <span class="text-slate-400">(hero, cta)</span></label>
            <input id="cta_label" type="text" name="cta_label" value="{{ old('cta_label', $section->cta_label ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="cta_url" class="block text-sm font-medium text-slate-700">CTA Button URL <span class="text-slate-400">(hero, cta)</span></label>
            <input id="cta_url" type="text" name="cta_url" value="{{ old('cta_url', $section->cta_url ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-slate-700">Image <span class="text-slate-400">(hero, about, founder quote: photo)</span></label>
        @if (isset($section) && $section->image_path)
            <img src="{{ asset('storage/'.$section->image_path) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="image" type="file" name="image" accept="image/*"
               class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['active', 'inactive'] as $option)
                <option value="{{ $option }}" @selected(old('status', $section->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($section) ? 'Update Section' : 'Create Section' }}
    </button>
</div>
