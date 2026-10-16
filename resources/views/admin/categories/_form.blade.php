@csrf
@isset($category) @method('PUT') @endisset

<x-form-errors />

<div class="d-flex flex-column gap-4 mw-lg">
    <div>
        <label for="name" class="d-block fs-sm fw-medium text-body-secondary">Category Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="image" class="d-block fs-sm fw-medium text-body-secondary">Image</label>
        @if (isset($category) && $category->image)
            <img src="{{ asset('storage/'.$category->image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="image" type="file" name="image" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
        <p class="mt-1 fs-xs text-secondary">JPG, PNG or WEBP &middot; max 2 MB. Shown on the category page and the homepage category grid.</p>
    </div>

    <div>
        <label for="description" class="d-block fs-sm fw-medium text-body-secondary">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="d-grid grid-cols-2 gap-4">
        <div>
            <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
            <select id="status" name="status" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $category->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="d-block fs-sm fw-medium text-body-secondary">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $category->display_order ?? 0) }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <fieldset class="border rounded-2 p-4">
        <legend class="fs-sm fw-medium text-body-secondary px-1">SEO</legend>
        <div class="d-flex flex-column gap-3">
            <div>
                <label for="meta_title" class="d-block fs-sm text-body-secondary">Meta Title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="meta_description" class="d-block fs-sm text-body-secondary">Meta Description</label>
                <input id="meta_description" type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($category) ? 'Update Category' : 'Create Category' }}
    </button>
</div>
