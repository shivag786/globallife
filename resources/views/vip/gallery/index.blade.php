<x-layouts.app title="Gallery" heading="Gallery">
    <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
        <h2 class="fw-semibold mb-3">Add Image</h2>
        <form method="POST" action="{{ route('vip.gallery.store') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-end gap-3">
            @csrf
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Image</label>
                <input type="file" name="image" accept="image/*" required class="mt-1 d-block fs-sm text-body-secondary">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Title (optional)</label>
                <input type="text" name="title" class="mt-1 rounded-2 shadow-sm fs-sm">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Sort Order</label>
                <input type="number" name="sort_order" value="0" class="mt-1 rounded-2 shadow-sm fs-sm w-24">
            </div>
            <button type="submit" class="btn btn-brand btn-sm">Upload</button>
        </form>
    </div>

    <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-4">
        @forelse ($items as $item)
            <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                <img src="{{ asset('storage/'.$item->image_path) }}" class="w-100 h-36 object-fit-cover">
                <form method="POST" action="{{ route('vip.gallery.update', $item) }}" class="p-3 d-flex flex-column gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="title" value="{{ $item->title }}" placeholder="Title"
                           class="w-100 rounded-2 shadow-sm fs-xs">
                    <div class="d-flex align-items-center justify-content-between fs-xs">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="is_visible" value="1" @checked($item->is_visible)>
                            Visible
                        </label>
                        <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="w-14 rounded-2 shadow-sm">
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-link p-0 text-decoration-none text-primary hover-underline fs-xs">Save</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('vip.gallery.destroy', $item) }}" data-confirm="Delete this image?" data-confirm-danger class="px-3 pb-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline fs-xs">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-secondary fs-sm col-span-full">No images yet.</p>
        @endforelse
    </div>
</x-layouts.app>
