@csrf
@isset($product) @method('PUT') @endisset

<div class="space-y-4 max-w-2xl">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Product Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="category" class="block text-sm font-medium text-slate-700">Category</label>
            <input id="category" type="text" name="category" value="{{ old('category', $product->category ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="badge" class="block text-sm font-medium text-slate-700">Badge <span class="text-slate-400">(e.g. New Launch, Bestseller)</span></label>
            <input id="badge" type="text" name="badge" value="{{ old('badge', $product->badge ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label for="tags" class="block text-sm font-medium text-slate-700">Tags <span class="text-slate-400">(comma separated)</span></label>
        <input id="tags" type="text" name="tags" value="{{ old('tags', isset($product) ? implode(', ', $product->tags ?? []) : '') }}"
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="short_description" class="block text-sm font-medium text-slate-700">Short Description</label>
        <input id="short_description" type="text" name="short_description" value="{{ old('short_description', $product->short_description ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="editor-long_description" class="block text-sm font-medium text-slate-700 mb-1">Long Description <span class="text-slate-400">(shown on detail page)</span></label>
        <x-ckeditor name="long_description" :value="$product->long_description ?? ''" :rows="6" />
    </div>

    <div>
        <label for="specs" class="block text-sm font-medium text-slate-700">Specifications <span class="text-slate-400">("Key | Value" per line)</span></label>
        <textarea id="specs" name="specs" rows="4"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('specs', isset($product) && $product->specs ? collect($product->specs)->map(fn($v, $k) => "$k | $v")->implode("\n") : '') }}</textarea>
    </div>

    <div>
        <label for="main_image" class="block text-sm font-medium text-slate-700">Main Image</label>
        @if (isset($product) && $product->main_image)
            <img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="main_image" type="file" name="main_image" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300" @checked(old('is_featured', $product->is_featured ?? false))>
        Feature on homepage product showcase
    </label>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $product->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="block text-sm font-medium text-slate-700">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $product->display_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <fieldset class="border border-slate-200 rounded-md p-4">
        <legend class="text-sm font-medium text-slate-700 px-1">SEO</legend>
        <div class="space-y-3">
            <div>
                <label for="meta_title" class="block text-sm text-slate-600">Meta Title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="meta_description" class="block text-sm text-slate-600">Meta Description</label>
                <input id="meta_description" type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="canonical_url" class="block text-sm text-slate-600">Canonical URL</label>
                <input id="canonical_url" type="text" name="canonical_url" value="{{ old('canonical_url', $product->canonical_url ?? '') }}"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($product) ? 'Update Product' : 'Create Product' }}
    </button>
</div>
