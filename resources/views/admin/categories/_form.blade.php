@csrf
@isset($category) @method('PUT') @endisset

<div class="space-y-4 max-w-lg">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Category Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-slate-700">Image</label>
        @if (isset($category) && $category->image)
            <img src="{{ asset('storage/'.$category->image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="image" type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $category->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="block text-sm font-medium text-slate-700">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $category->display_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <fieldset class="border border-slate-200 rounded-md p-4">
        <legend class="text-sm font-medium text-slate-700 px-1">SEO</legend>
        <div class="space-y-3">
            <div>
                <label for="meta_title" class="block text-sm text-slate-600">Meta Title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="meta_description" class="block text-sm text-slate-600">Meta Description</label>
                <input id="meta_description" type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($category) ? 'Update Category' : 'Create Category' }}
    </button>
</div>
