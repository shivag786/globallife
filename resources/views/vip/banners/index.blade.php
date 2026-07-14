<x-layouts.app title="Homepage Banner" heading="Homepage Banner">
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">Add Banner Slide</h2>
        <form method="POST" action="{{ route('vip.banners.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Device</label>
                <select name="device" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
                    <option value="desktop">Desktop</option>
                    <option value="mobile">Mobile</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Image</label>
                <input type="file" name="image" accept="image/*" required class="mt-1 block text-sm text-slate-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Heading</label>
                <input type="text" name="heading" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Sub Heading</label>
                <input type="text" name="subheading" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Button Text</label>
                <input type="text" name="button_text" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Button Link</label>
                <input type="text" name="button_link" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">Add Slide</button>
            </div>
        </form>
    </div>

    @foreach (['Desktop' => $desktopBanners, 'Mobile' => $mobileBanners] as $label => $banners)
        <h2 class="font-semibold mb-3">{{ $label }} Slides</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @forelse ($banners as $banner)
                <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                    <img src="{{ asset('storage/'.$banner->image_path) }}" class="w-full h-32 object-cover">
                    <div class="p-3">
                        <p class="text-sm font-medium truncate">{{ $banner->heading ?: 'Untitled' }}</p>
                        <form method="POST" action="{{ route('vip.banners.update', $banner) }}" class="mt-2 flex items-center justify-between">
                            @csrf
                            @method('PUT')
                            <label class="flex items-center gap-1.5 text-xs">
                                <input type="checkbox" name="is_visible" value="1" @checked($banner->is_visible) onchange="this.form.submit()">
                                Visible
                            </label>
                        </form>
                        <form method="POST" action="{{ route('vip.banners.destroy', $banner) }}" data-confirm="Remove this slide?" data-confirm-danger class="mt-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-sm col-span-full">No {{ strtolower($label) }} slides yet.</p>
            @endforelse
        </div>
    @endforeach
</x-layouts.app>
