<x-layouts.app title="Media Library" heading="Media Library">
    <p class="text-sm text-slate-500 mb-4">Upload images here to use them in the homepage Gallery section.</p>

    @can('media.create')
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-lg shadow-sm border border-slate-100 p-6 mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-slate-700">Title</label>
                <input id="title" type="text" name="title" required
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700">Category</label>
                <input id="category" type="text" name="category" placeholder="Gallery / Certificates / Events"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="alt_text" class="block text-sm font-medium text-slate-700">Alt Text</label>
                <input id="alt_text" type="text" name="alt_text"
                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="file" class="block text-sm font-medium text-slate-700">Image</label>
                <input id="file" type="file" name="file" accept="image/*" required class="mt-1 block w-full text-sm text-slate-600">
            </div>
            <input type="hidden" name="status" value="active">
            <div class="sm:col-span-2">
                <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
                    Upload
                </button>
            </div>
        </form>
    @endcan

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse ($items as $item)
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->alt_text ?? $item->title }}" class="w-full aspect-square object-cover">
                <div class="p-3">
                    <p class="text-sm font-medium truncate">{{ $item->title }}</p>
                    <p class="text-xs text-slate-400">{{ $item->category ?? 'Uncategorized' }}</p>
                    <div class="flex items-center justify-between mt-2">
                        @can('media.edit')
                            <form action="{{ route('admin.media.toggle-status', $item) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-2 py-0.5 rounded text-xs {{ $item->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $item->status }}
                                </button>
                            </form>
                        @endcan
                        @can('media.delete')
                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST" data-confirm="Delete this image?" data-confirm-danger>
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs hover:underline">Delete</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-slate-400 py-16">No media uploaded yet.</p>
        @endforelse
    </div>
</x-layouts.app>
