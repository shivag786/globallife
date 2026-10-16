@csrf
@isset($brand) @method('PUT') @endisset

<div class="d-flex flex-column gap-4 mw-lg">
    <div>
        <label for="name" class="d-block fs-sm fw-medium text-body-secondary">Brand Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="logo" class="d-block fs-sm fw-medium text-body-secondary">Logo</label>
        @if (isset($brand) && $brand->logo)
            <img src="{{ asset('storage/'.$brand->logo) }}" alt="" class="h-16 mt-1 mb-2 rounded bg-body-tertiary">
        @endif
        <input id="logo" type="file" name="logo" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
    </div>

    <div>
        <label for="description" class="d-block fs-sm fw-medium text-body-secondary">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('description', $brand->description ?? '') }}</textarea>
    </div>

    <div class="d-grid grid-cols-2 gap-4">
        <div>
            <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
            <select id="status" name="status" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $brand->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="d-block fs-sm fw-medium text-body-secondary">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $brand->display_order ?? 0) }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($brand) ? 'Update Brand' : 'Create Brand' }}
    </button>
</div>
