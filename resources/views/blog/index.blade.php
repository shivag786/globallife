<x-layouts.public title="Blog" :metaDescription="'Wellness tips, product guides, and updates from Global Life.'">
    <div class="mw-5xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display fs-4xl fw-bold text-brand-emphasis mb-3">From the Blog</h1>
            <p class="text-secondary">Wellness tips, product guides, and company updates.</p>
        </div>

        <div class="d-grid grid-cols-sm-2 gap-8">
            @forelse ($posts as $index => $post)
                <a href="{{ route('blog.show', $post) }}"
                   class="reveal d-block bg-body border rounded-4 overflow-hidden premium-shadow"
                   style="transition-delay: {{ ($index % 6) * 0.08 }}s">
                    <div class="aspect-video bg-brand-subtle overflow-hidden">
                        @if ($post->featured_image)
                            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover">
                        @endif
                    </div>
                    <div class="p-6">
                        @if ($post->category)
                            <span class="fs-xs fw-semibold text-brand text-uppercase tracking-wide">{{ $post->category }}</span>
                        @endif
                        <h2 class="font-display fs-lg fw-semibold text-brand-emphasis mt-1 mb-2">{{ $post->title }}</h2>
                        <p class="fs-sm text-secondary">{{ $post->excerpt }}</p>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <p class="fs-xs text-secondary">{{ $post->published_at?->format('M j, Y') }}</p>
                            <div class="d-flex align-items-center gap-3 fs-xs text-secondary">
                                <span class="d-flex align-items-center gap-1"><x-icon name="eye" class="w-3.5 h-3.5" /> {{ $post->views }}</span>
                                <span class="d-flex align-items-center gap-1"><x-icon name="heart" class="w-3.5 h-3.5" /> {{ $post->likes_count }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-secondary py-16">No posts published yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    </div>
</x-layouts.public>
