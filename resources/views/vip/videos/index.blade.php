<x-layouts.app title="YouTube Videos" heading="YouTube Videos">
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6">
        <h2 class="font-semibold mb-3">Add Video ({{ $videos->count() }}/6)</h2>
        @if ($videos->count() < 6)
            <form method="POST" action="{{ route('vip.videos.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-sm font-medium text-slate-700">YouTube URL</label>
                    <input type="url" name="youtube_url" required placeholder="https://youtube.com/watch?v=..."
                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Title (optional)</label>
                    <input type="text" name="title" class="mt-1 rounded-md border-slate-300 shadow-sm text-sm">
                </div>
                <button type="submit" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">Add</button>
            </form>
        @else
            <p class="text-sm text-slate-400">You've reached the 6-video limit. Remove one to add another.</p>
        @endif
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($videos as $video)
            <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
                <img src="{{ $video->thumbnail_url }}" class="w-full h-36 object-cover">
                <div class="p-3">
                    <p class="text-sm font-medium truncate">{{ $video->title ?: $video->youtube_url }}</p>
                    <form method="POST" action="{{ route('vip.videos.destroy', $video) }}" onsubmit="return confirm('Remove this video?')" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-slate-400 text-sm col-span-full">No videos yet.</p>
        @endforelse
    </div>
</x-layouts.app>
