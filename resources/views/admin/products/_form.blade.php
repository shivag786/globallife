@csrf
@isset($product) @method('PUT') @endisset

@php
    $categories = \App\Models\Category::active()->orderBy('display_order')->orderBy('name')->get();
    $brands = \App\Models\Brand::active()->orderBy('display_order')->orderBy('name')->get();
@endphp

<div class="space-y-4 max-w-2xl">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Product Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="category_id" class="block text-sm font-medium text-slate-700">Category</label>
            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— Select category —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if ($categories->isEmpty())
                <p class="mt-1 text-xs text-amber-600">No categories yet — <a href="{{ route('admin.categories.create') }}" class="underline">add one</a>.</p>
            @endif
        </div>
        <div>
            <label for="brand_id" class="block text-sm font-medium text-slate-700">Brand</label>
            <select id="brand_id" name="brand_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— No brand —</option>
                @foreach ($brands as $b)
                    <option value="{{ $b->id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="sku" class="block text-sm font-medium text-slate-700">SKU <span class="text-slate-400">(unique code)</span></label>
            <input id="sku" type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="badge" class="block text-sm font-medium text-slate-700">Badge <span class="text-slate-400">(e.g. Bestseller)</span></label>
            <input id="badge" type="text" name="badge" value="{{ old('badge', $product->badge ?? '') }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="mrp" class="block text-sm font-medium text-slate-700">MRP <span class="text-slate-400">(₹)</span></label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">₹</span>
                <input id="mrp" type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', $product->mrp ?? '') }}" placeholder="0.00"
                       class="block w-full rounded-md border-slate-300 shadow-sm pl-7 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-slate-700">Selling Price <span class="text-slate-400">(₹)</span></label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">₹</span>
                <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price ?? '') }}" placeholder="0.00"
                       class="block w-full rounded-md border-slate-300 shadow-sm pl-7 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
        <div>
            <label for="offer_price" class="block text-sm font-medium text-slate-700">Offer Price <span class="text-slate-400">(₹)</span></label>
            <div class="relative mt-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">₹</span>
                <input id="offer_price" type="number" step="0.01" min="0" name="offer_price" value="{{ old('offer_price', $product->offer_price ?? '') }}" placeholder="0.00"
                       class="block w-full rounded-md border-slate-300 shadow-sm pl-7 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
    </div>
    <p class="text-xs text-slate-400 -mt-2">The customer pays the Offer Price when set, otherwise the Selling Price. MRP shows as a strikethrough. Commission is calculated on the price paid.</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="stock" class="block text-sm font-medium text-slate-700">Stock</label>
            <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
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
        <label for="ingredients" class="block text-sm font-medium text-slate-700">Ingredients</label>
        <textarea id="ingredients" name="ingredients" rows="3"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('ingredients', $product->ingredients ?? '') }}</textarea>
    </div>

    <div>
        <label for="usage_instructions" class="block text-sm font-medium text-slate-700">Usage Instructions</label>
        <textarea id="usage_instructions" name="usage_instructions" rows="3"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('usage_instructions', $product->usage_instructions ?? '') }}</textarea>
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

    <div>
        <label for="gallery" class="block text-sm font-medium text-slate-700">Gallery Images <span class="text-slate-400">(multiple)</span></label>
        @if (isset($product) && $product->gallery)
            <div class="flex flex-wrap gap-2 mt-2 mb-2">
                @foreach ($product->gallery as $img)
                    <img src="{{ asset('storage/'.$img) }}" alt="" class="h-16 w-16 object-cover rounded border border-slate-200">
                @endforeach
            </div>
            <label class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <input type="checkbox" name="remove_gallery" value="1" class="rounded border-slate-300"> Remove all current gallery images
            </label>
        @endif
        <input id="gallery" type="file" name="gallery[]" accept="image/*" multiple class="mt-1 block w-full text-sm text-slate-600">
        <p class="text-xs text-slate-400 mt-1">New images are added to the gallery. Max 2MB each.</p>
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300" @checked(old('is_featured', $product->is_featured ?? false))>
        Feature on homepage product showcase
    </label>

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
