@csrf
@isset($brand) @method('PUT') @endisset

<div class="space-y-4 max-w-lg">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Brand Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="logo" class="block text-sm font-medium text-slate-700">Logo</label>
        @if (isset($brand) && $brand->logo)
            <img src="{{ asset('storage/'.$brand->logo) }}" alt="" class="h-16 mt-1 mb-2 rounded bg-slate-50">
        @endif
        <input id="logo" type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $brand->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $brand->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="block text-sm font-medium text-slate-700">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $brand->display_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($brand) ? 'Update Brand' : 'Create Brand' }}
    </button>
</div>
