<x-layouts.app title="Homepage Banner" heading="Homepage Banner">
    <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
        <h2 class="fw-semibold mb-3">Add Banner Slide</h2>
        <form method="POST" action="{{ route('vip.banners.store') }}" enctype="multipart/form-data" class="d-grid grid-cols-sm-2 gap-3">
            @csrf
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Device</label>
                <select name="device" class="mt-1 d-block w-100 rounded-2 fs-sm">
                    <option value="desktop">Desktop</option>
                    <option value="mobile">Mobile</option>
                </select>
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Image</label>
                <input type="file" name="image" accept="image/*" required class="mt-1 d-block fs-sm text-body-secondary">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Heading</label>
                <input type="text" name="heading" class="mt-1 d-block w-100 rounded-2 fs-sm">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Sub Heading</label>
                <input type="text" name="subheading" class="mt-1 d-block w-100 rounded-2 fs-sm">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Button Text</label>
                <input type="text" name="button_text" class="mt-1 d-block w-100 rounded-2 fs-sm">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Button Link</label>
                <input type="text" name="button_link" class="mt-1 d-block w-100 rounded-2 fs-sm">
            </div>
            <div class="col-span-sm-2">
                <button type="submit" class="btn btn-brand btn-sm">Add Slide</button>
            </div>
        </form>
    </div>

    @foreach (['Desktop' => $desktopBanners, 'Mobile' => $mobileBanners] as $label => $banners)
        <h2 class="fw-semibold mb-3">{{ $label }} Slides</h2>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-4 mb-8">
            @forelse ($banners as $banner)
                <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                    <img src="{{ asset('storage/'.$banner->image_path) }}" class="w-100 h-32 object-fit-cover">
                    <div class="p-3">
                        <p class="fs-sm fw-medium text-truncate">{{ $banner->heading ?: 'Untitled' }}</p>
                        <form method="POST" action="{{ route('vip.banners.update', $banner) }}" class="mt-2 d-flex align-items-center justify-content-between">
                            @csrf
                            @method('PUT')
                            <label class="d-flex align-items-center gap-2 fs-xs">
                                <input type="checkbox" name="is_visible" value="1" @checked($banner->is_visible) onchange="this.form.submit()">
                                Visible
                            </label>
                        </form>
                        <form method="POST" action="{{ route('vip.banners.destroy', $banner) }}" data-confirm="Remove this slide?" data-confirm-danger class="mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline fs-xs">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-secondary fs-sm col-span-full">No {{ strtolower($label) }} slides yet.</p>
            @endforelse
        </div>
    @endforeach
</x-layouts.app>
