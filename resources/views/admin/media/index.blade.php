<x-layouts.app title="Media Library" heading="Media Library">
    <p class="fs-sm text-secondary mb-4">Upload images here to use them in the homepage Gallery section.</p>

    @can('media.create')
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
              class="bg-body rounded-2 shadow-sm border p-6 mb-6 d-grid grid-cols-1 grid-cols-sm-2 gap-4">
            @csrf
            <div>
                <label for="title" class="d-block fs-sm fw-medium text-body-secondary">Title</label>
                <input id="title" type="text" name="title" required
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="category" class="d-block fs-sm fw-medium text-body-secondary">Category</label>
                <input id="category" type="text" name="category" placeholder="Gallery / Certificates / Events"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="alt_text" class="d-block fs-sm fw-medium text-body-secondary">Alt Text</label>
                <input id="alt_text" type="text" name="alt_text"
                       class="mt-1 d-block w-100 rounded-2 shadow-sm">
            </div>
            <div>
                <label for="file" class="d-block fs-sm fw-medium text-body-secondary">Image</label>
                <input id="file" type="file" name="file" accept="image/*" required class="mt-1 d-block w-100 fs-sm text-body-secondary">
            </div>
            <input type="hidden" name="status" value="active">
            <div class="col-span-sm-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    Upload
                </button>
            </div>
        </form>
    @endcan

    <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-4">
        @forelse ($items as $item)
            <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->alt_text ?? $item->title }}" class="w-100 aspect-square object-fit-cover">
                <div class="p-3">
                    <p class="fs-sm fw-medium text-truncate">{{ $item->title }}</p>
                    <p class="fs-xs text-secondary">{{ $item->category ?? 'Uncategorized' }}</p>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        @can('media.edit')
                            <form action="{{ route('admin.media.toggle-status', $item) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-2 py-1 rounded fs-xs {{ $item->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                    {{ $item->status }}
                                </button>
                            </form>
                        @endcan
                        @can('media.delete')
                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST" data-confirm="Delete this image?" data-confirm-danger>
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger fs-xs hover-underline">Delete</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-secondary py-16">No media uploaded yet.</p>
        @endforelse
    </div>
</x-layouts.app>
