@csrf
@isset($product) @method('PUT') @endisset

@php
    $categories = \App\Models\Category::active()->orderBy('display_order')->orderBy('name')->get();
    $brands = \App\Models\Brand::active()->orderBy('display_order')->orderBy('name')->get();
@endphp

<div class="d-flex flex-column gap-4 mw-2xl">
    <div>
        <label for="name" class="d-block fs-sm fw-medium text-body-secondary">Product Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="category_id" class="d-block fs-sm fw-medium text-body-secondary">Category</label>
            <select id="category_id" name="category_id" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                <option value="">— Select category —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if ($categories->isEmpty())
                <p class="mt-1 fs-xs text-warning">No categories yet — <a href="{{ route('admin.categories.create') }}" class="text-decoration-underline">add one</a>.</p>
            @endif
        </div>
        <div>
            <label for="brand_id" class="d-block fs-sm fw-medium text-body-secondary">Brand</label>
            <select id="brand_id" name="brand_id" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                <option value="">— No brand —</option>
                @foreach ($brands as $b)
                    <option value="{{ $b->id }}" @selected((string) old('brand_id', $product->brand_id ?? '') === (string) $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="sku" class="d-block fs-sm fw-medium text-body-secondary">SKU <span class="text-secondary">(unique code)</span></label>
            <input id="sku" type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="badge" class="d-block fs-sm fw-medium text-body-secondary">Badge <span class="text-secondary">(e.g. Bestseller)</span></label>
            <input id="badge" type="text" name="badge" value="{{ old('badge', $product->badge ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-3 gap-4">
        <div>
            <label for="mrp" class="d-block fs-sm fw-medium text-body-secondary">MRP <span class="text-secondary">(₹)</span></label>
            <div class="position-relative mt-1">
                <span class="position-absolute top-0 bottom-0 start-0 d-flex align-items-center ps-3 text-secondary">₹</span>
                <input id="mrp" type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', $product->mrp ?? '') }}" placeholder="0.00"
                       class="d-block w-100 rounded-2 shadow-sm ps-7">
            </div>
        </div>
        <div>
            <label for="price" class="d-block fs-sm fw-medium text-body-secondary">Selling Price <span class="text-secondary">(₹)</span></label>
            <div class="position-relative mt-1">
                <span class="position-absolute top-0 bottom-0 start-0 d-flex align-items-center ps-3 text-secondary">₹</span>
                <input id="price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price ?? '') }}" placeholder="0.00"
                       class="d-block w-100 rounded-2 shadow-sm ps-7">
            </div>
        </div>
        <div>
            <label for="offer_price" class="d-block fs-sm fw-medium text-body-secondary">Offer Price <span class="text-secondary">(₹)</span></label>
            <div class="position-relative mt-1">
                <span class="position-absolute top-0 bottom-0 start-0 d-flex align-items-center ps-3 text-secondary">₹</span>
                <input id="offer_price" type="number" step="0.01" min="0" name="offer_price" value="{{ old('offer_price', $product->offer_price ?? '') }}" placeholder="0.00"
                       class="d-block w-100 rounded-2 shadow-sm ps-7">
            </div>
        </div>
    </div>
    <p class="fs-xs text-secondary -n">The customer pays the Offer Price when set, otherwise the Selling Price. MRP shows as a strikethrough. Commission is calculated on the price paid.</p>

    <div class="d-grid grid-cols-1 grid-cols-sm-3 gap-4">
        <div>
            <label for="stock" class="d-block fs-sm fw-medium text-body-secondary">Stock</label>
            <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
            <select id="status" name="status" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $product->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="d-block fs-sm fw-medium text-body-secondary">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $product->display_order ?? 0) }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <div>
        <label for="tags" class="d-block fs-sm fw-medium text-body-secondary">Tags <span class="text-secondary">(comma separated)</span></label>
        <input id="tags" type="text" name="tags" value="{{ old('tags', isset($product) ? implode(', ', $product->tags ?? []) : '') }}"
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="short_description" class="d-block fs-sm fw-medium text-body-secondary">Short Description</label>
        <input id="short_description" type="text" name="short_description" value="{{ old('short_description', $product->short_description ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="editor-long_description" class="d-block fs-sm fw-medium text-body-secondary mb-1">Long Description <span class="text-secondary">(shown on detail page)</span></label>
        <x-ckeditor name="long_description" :value="$product->long_description ?? ''" :rows="6" />
    </div>

    <div>
        <label for="ingredients" class="d-block fs-sm fw-medium text-body-secondary">Ingredients</label>
        <textarea id="ingredients" name="ingredients" rows="3"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('ingredients', $product->ingredients ?? '') }}</textarea>
    </div>

    <div>
        <label for="usage_instructions" class="d-block fs-sm fw-medium text-body-secondary">Usage Instructions</label>
        <textarea id="usage_instructions" name="usage_instructions" rows="3"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('usage_instructions', $product->usage_instructions ?? '') }}</textarea>
    </div>

    <div>
        <label for="specs" class="d-block fs-sm fw-medium text-body-secondary">Specifications <span class="text-secondary">("Key | Value" per line)</span></label>
        <textarea id="specs" name="specs" rows="4"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('specs', isset($product) && $product->specs ? collect($product->specs)->map(fn($v, $k) => "$k | $v")->implode("\n") : '') }}</textarea>
    </div>

    <div>
        <label for="main_image" class="d-block fs-sm fw-medium text-body-secondary">Main Image</label>
        @if (isset($product) && $product->main_image)
            <img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="main_image" type="file" name="main_image" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
    </div>

    <div>
        <label for="gallery" class="d-block fs-sm fw-medium text-body-secondary">Gallery Images <span class="text-secondary">(multiple)</span></label>
        @if (isset($product) && $product->gallery)
            <div class="d-flex flex-wrap gap-2 mt-2 mb-2">
                @foreach ($product->gallery as $img)
                    <img src="{{ asset('storage/'.$img) }}" alt="" class="h-16 w-16 object-fit-cover rounded border">
                @endforeach
            </div>
            <label class="d-flex align-items-center gap-2 fs-xs text-secondary mb-1">
                <input type="checkbox" name="remove_gallery" value="1" class="rounded"> Remove all current gallery images
            </label>
        @endif
        <input id="gallery" type="file" name="gallery[]" accept="image/*" multiple class="mt-1 d-block w-100 fs-sm text-body-secondary">
        <p class="fs-xs text-secondary mt-1">New images are added to the gallery. Max 2MB each.</p>
    </div>

    <label class="d-flex align-items-center gap-2 fs-sm">
        <input type="checkbox" name="is_featured" value="1" class="rounded" @checked(old('is_featured', $product->is_featured ?? false))>
        Feature on homepage product showcase
    </label>

    <fieldset class="border rounded-2 p-4">
        <legend class="fs-sm fw-medium text-body-secondary px-1">SEO</legend>
        <div class="d-flex flex-column gap-3">
            <div>
                <label for="meta_title" class="d-block fs-sm text-body-secondary">Meta Title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="meta_description" class="d-block fs-sm text-body-secondary">Meta Description</label>
                <input id="meta_description" type="text" name="meta_description" value="{{ old('meta_description', $product->meta_description ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="canonical_url" class="d-block fs-sm text-body-secondary">Canonical URL</label>
                <input id="canonical_url" type="text" name="canonical_url" value="{{ old('canonical_url', $product->canonical_url ?? '') }}"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($product) ? 'Update Product' : 'Create Product' }}
    </button>
</div>
