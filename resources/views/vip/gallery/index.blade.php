<x-layouts.app title="Gallery" heading="Gallery">
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">Add Image</h2>
        <form method="POST" action="{{ route('vip.gallery.store') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Image</label>
                <input type="file" name="image" accept="image/*" required class="mt-1 block text-sm text-slate-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Title (optional)</label>
                <input type="text" name="title" class="mt-1 rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Sort Order</label>
                <input type="number" name="sort_order" value="0" class="mt-1 rounded-md border-slate-300 shadow-sm text-sm w-24">
            </div>
            <button type="submit" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">Upload</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                <img src="{{ asset('storage/'.$item->image_path) }}" class="w-full h-36 object-cover">
                <form method="POST" action="{{ route('vip.gallery.update', $item) }}" class="p-3 space-y-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="title" value="{{ $item->title }}" placeholder="Title"
                           class="w-full rounded-md border-slate-300 shadow-sm text-xs">
                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" name="is_visible" value="1" @checked($item->is_visible)>
                            Visible
                        </label>
                        <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="w-14 rounded-md border-slate-300 shadow-sm">
                    </div>
                    <div class="flex justify-between">
                        <button type="submit" class="text-indigo-600 hover:underline text-xs">Save</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('vip.gallery.destroy', $item) }}" data-confirm="Delete this image?" data-confirm-danger class="px-3 pb-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-slate-400 text-sm col-span-full">No images yet.</p>
        @endforelse
    </div>
</x-layouts.app>
