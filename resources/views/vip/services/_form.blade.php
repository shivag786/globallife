@csrf
@isset($service) @method('PUT') @endisset

<div class="bootstrap-scope max-w-3xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Service Name" :value="$service->name ?? ''"
                                       required data-slug-source />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="category" label="Category" :value="$service->category ?? ''" />
                    </div>
                    {{-- The slug is generated from the name server-side; this is a
                         read-only mirror so the member can see the SEO title and URL
                         they are about to get. It is deliberately not submitted. --}}
                    <div class="col-12">
                        <label for="service-slug" class="form-label small fw-medium text-slate-700 mb-1">
                            SEO Title &amp; Page URL
                        </label>
                        <input type="text" id="service-slug" class="form-control bg-light" disabled
                               data-slug-preview value="{{ $service->slug ?? '' }}"
                               placeholder="fills-in-from-the-service-name">
                        <p class="small text-muted mt-1 mb-0">
                            Built from the service name, words joined by dashes. Renaming the service
                            changes it.
                        </p>
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

        <div class="card shadow-sm border-0" data-price-preview>
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Pricing</h2>
                <p class="small text-muted mb-0">
                    Enter the MRP, the sale price, or both. The discount works itself out.
                </p>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <x-forms.input name="mrp" type="number" step="0.01" min="0" label="MRP"
                                       :value="$service->mrp ?? ''" data-price-mrp
                                       help="The full price, struck through when a sale price is set." />
                    </div>
                    <div class="col-md-4">
                        <x-forms.input name="offer_price" type="number" step="0.01" min="0" label="Sale Price"
                                       :value="$service->offer_price ?? ''" data-price-sale
                                       help="What the customer actually pays." />
                    </div>
                    {{-- Live mirror of what the public card will show. The real figures
                         are derived server-side on save; this is only a preview. --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-medium text-slate-700 mb-1">Customers will see</label>
                        <div class="border rounded-3 bg-light px-3 py-2" style="min-height: 2.75rem">
                            <div class="d-flex align-items-baseline gap-2 flex-wrap" data-price-output>
                                <span class="text-muted small">Enter a price to preview</span>
                            </div>
                        </div>
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
