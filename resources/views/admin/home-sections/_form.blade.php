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

<div class="d-flex flex-column gap-4 mw-2xl">
    <div>
        <label for="type" class="d-block fs-sm fw-medium text-body-secondary">Section Type</label>
        <select id="type" name="type" class="mt-1 d-block w-100 rounded-2 shadow-sm">
            @foreach (\App\Models\HomeSection::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $section->type ?? 'hero') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="title" class="d-block fs-sm fw-medium text-body-secondary">Title / Heading <span class="text-secondary">(founder quote: founder's name)</span></label>
        <input id="title" type="text" name="title" value="{{ old('title', $section->title ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="subtitle" class="d-block fs-sm fw-medium text-body-secondary">Subtitle <span class="text-secondary">(hero, cta, founder quote: role/title)</span></label>
        <input id="subtitle" type="text" name="subtitle" value="{{ old('subtitle', $section->subtitle ?? '') }}"
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="editor-content" class="d-block fs-sm fw-medium text-body-secondary mb-1">Body Content <span class="text-secondary">(about, founder quote, business opportunity)</span></label>
        <x-ckeditor name="content" :value="$section->content ?? ''" :rows="6" />
    </div>

    <div>
        <label for="items" class="d-block fs-sm fw-medium text-body-secondary">
            Items <span class="text-secondary">(features/business opportunity/process steps/quality: "Title | Description" per line &middot; stats: "Label | Value" per line &middot; team: "Name | Role" per line &middot; certifications: "Badge | Detail" per line &middot; hero: trust badge label per line, optional)</span>
        </label>
        <textarea id="items" name="items" rows="5"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('items', $itemsText) }}</textarea>
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="cta_label" class="d-block fs-sm fw-medium text-body-secondary">CTA Button Label <span class="text-secondary">(hero, cta)</span></label>
            <input id="cta_label" type="text" name="cta_label" value="{{ old('cta_label', $section->cta_label ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="cta_url" class="d-block fs-sm fw-medium text-body-secondary">CTA Button URL <span class="text-secondary">(hero, cta)</span></label>
            <input id="cta_url" type="text" name="cta_url" value="{{ old('cta_url', $section->cta_url ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <div>
        <label for="image" class="d-block fs-sm fw-medium text-body-secondary">Image <span class="text-secondary">(hero, about, founder quote: photo)</span></label>
        @if (isset($section) && $section->image_path)
            <img src="{{ asset('storage/'.$section->image_path) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="image" type="file" name="image" accept="image/*"
               class="mt-1 d-block w-100 fs-sm text-body-secondary">
    </div>

    <div>
        <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
        <select id="status" name="status" class="mt-1 d-block w-100 rounded-2 shadow-sm">
            @foreach (['active', 'inactive'] as $option)
                <option value="{{ $option }}" @selected(old('status', $section->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($section) ? 'Update Section' : 'Create Section' }}
    </button>
</div>
