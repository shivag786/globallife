@csrf
@isset($product) @method('PUT') @endisset

<div class="bootstrap-scope max-w-3xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Product Name" :value="$product->name ?? ''" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="category" label="Category" :value="$product->category ?? ''" />
                    </div>
                    <div class="col-12">
                        <x-forms.input name="short_description" label="Short Description" :value="$product->short_description ?? ''" />
                    </div>
                    <div class="col-12">
                        <x-forms.input name="long_description" label="Long Description" as="textarea" :value="$product->long_description ?? ''" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="tags" label="Tags" :value="isset($product) ? implode(', ', $product->tags ?? []) : ''" help="Comma-separated" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.file-input name="image" label="Product Image" :current="$product->image_path ?? null" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Pricing</h2></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <x-forms.input name="mrp" type="number" step="0.01" label="MRP" :value="$product->mrp ?? ''" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.input name="offer_price" type="number" step="0.01" label="Offer Price" :value="$product->offer_price ?? ''" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.input name="discount_percent" type="number" step="0.01" label="Discount %" :value="$product->discount_percent ?? ''" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.input name="strike_price" type="number" step="0.01" label="Strike Price" :value="$product->strike_price ?? ''" />
                    </div>
                    <div class="col-12">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="show_pricing" value="1" @checked($product->show_pricing ?? true)>
                            <span class="text-sm">Show pricing on public page</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Inventory Details</h2></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <x-forms.input name="sku" label="SKU" :value="$product->sku ?? ''" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="stock" type="number" label="Stock" :value="$product->stock ?? ''" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="brand" label="Brand" :value="$product->brand ?? ''" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="weight" label="Weight" :value="$product->weight ?? ''" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="color" label="Color" :value="$product->color ?? ''" />
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="variant" label="Variant" :value="$product->variant ?? ''" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <x-forms.input name="status" label="Status" as="select" :value="$product->status ?? 'published'">
                            <option value="published" @selected(old('status', $product->status ?? 'published') === 'published')>Published</option>
                            <option value="draft" @selected(old('status', $product->status ?? '') === 'draft')>Draft</option>
                        </x-forms.input>
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="sort_order" type="number" label="Sort Order" :value="$product->sort_order ?? 0" />
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <label class="d-flex align-items-center gap-2 mb-2">
                            <input type="checkbox" name="is_featured" value="1" @checked($product->is_featured ?? false)>
                            <span class="text-sm">Featured</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                {{ isset($product) ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </div>
</div>
