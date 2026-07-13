@csrf
@isset($service) @method('PUT') @endisset

<div class="bootstrap-scope max-w-3xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Service Name" :value="$service->name ?? ''" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="category" label="Category" :value="$service->category ?? ''" />
                    </div>
                    <div class="col-12">
                        <x-forms.input name="short_description" label="Short Description" :value="$service->short_description ?? ''" />
                    </div>
                    <div class="col-12">
                        <x-forms.input name="long_description" label="Long Description" as="textarea" :value="$service->long_description ?? ''" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="tags" label="Tags" :value="isset($service) ? implode(', ', $service->tags ?? []) : ''" help="Comma-separated" />
                    </div>
                    <div class="col-md-6">
                        <x-forms.file-input name="image" label="Service Image" :current="$service->image_path ?? null" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h2 class="h6 mb-0 fw-semibold">Pricing</h2></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <x-forms.input name="mrp" type="number" step="0.01" label="MRP" :value="$service->mrp ?? ''" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.input name="offer_price" type="number" step="0.01" label="Offer Price" :value="$service->offer_price ?? ''" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.input name="discount_percent" type="number" step="0.01" label="Discount %" :value="$service->discount_percent ?? ''" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.input name="strike_price" type="number" step="0.01" label="Strike Price" :value="$service->strike_price ?? ''" />
                    </div>
                    <div class="col-md-6">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="show_pricing" value="1" @checked($service->show_pricing ?? true)>
                            <span class="text-sm">Show pricing on public page</span>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="show_book_now" value="1" @checked($service->show_book_now ?? true)>
                            <span class="text-sm">Show "Book Now" button</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <x-forms.input name="status" label="Status" as="select" :value="$service->status ?? 'published'">
                            <option value="published" @selected(old('status', $service->status ?? 'published') === 'published')>Published</option>
                            <option value="draft" @selected(old('status', $service->status ?? '') === 'draft')>Draft</option>
                        </x-forms.input>
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="sort_order" type="number" label="Sort Order" :value="$service->sort_order ?? 0" />
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <label class="d-flex align-items-center gap-2 mb-2">
                            <input type="checkbox" name="is_featured" value="1" @checked($service->is_featured ?? false)>
                            <span class="text-sm">Featured</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                {{ isset($service) ? 'Update Service' : 'Create Service' }}
            </button>
        </div>
    </div>
</div>
