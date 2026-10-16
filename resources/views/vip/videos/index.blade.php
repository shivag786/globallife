<x-layouts.app title="YouTube Videos" heading="YouTube Videos">
    <div class="bg-body rounded-2 shadow-sm border p-5 mb-6">
        <h2 class="fw-semibold mb-3">Add Video ({{ $videos->count() }}/6)</h2>
        @if ($videos->count() < 6)
            <form method="POST" action="{{ route('vip.videos.store') }}" class="d-flex flex-wrap align-items-end gap-3">
                @csrf
                <div class="flex-grow-1">
                    <label class="d-block fs-sm fw-medium text-body-secondary">YouTube URL</label>
                    <input type="url" name="youtube_url" required placeholder="https://youtube.com/watch?v=..."
                           class="mt-1 d-block w-100 rounded-2 shadow-sm fs-sm">
                </div>
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Title (optional)</label>
                    <input type="text" name="title" class="mt-1 rounded-2 shadow-sm fs-sm">
                </div>
                <button type="submit" class="btn btn-brand btn-sm">Add</button>
            </form>
        @else
            <p class="fs-sm text-secondary">You've reached the 6-video limit. Remove one to add another.</p>
        @endif
    </div>

    <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-4">
        @forelse ($videos as $video)
            <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                <img src="{{ $video->thumbnail_url }}" class="w-100 h-36 object-fit-cover">
                <div class="p-3">
                    <p class="fs-sm fw-medium text-truncate">{{ $video->title ?: $video->youtube_url }}</p>
                    <form method="POST" action="{{ route('vip.videos.destroy', $video) }}" data-confirm="Remove this video?" data-confirm-danger class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline fs-xs">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-secondary fs-sm col-span-full">No videos yet.</p>
        @endforelse
    </div>
</x-layouts.app>
