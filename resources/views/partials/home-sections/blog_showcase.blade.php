@php $posts = app(\App\Repositories\BlogPostRepository::class)->latestPublished(3); @endphp
@if ($posts->isNotEmpty())
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-3 gap-6">
            @foreach ($posts as $index => $post)
                <a href="{{ route('blog.show', $post) }}"
                   class="reveal d-block bg-body border rounded-4 overflow-hidden premium-shadow"
                   style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="aspect-video bg-brand-subtle overflow-hidden">
                        @if ($post->featured_image)
                            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover">
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-display fw-semibold text-brand-emphasis mb-2">{{ $post->title }}</h3>
                        <div class="d-flex align-items-center gap-3 fs-xs text-secondary">
                            <span class="d-flex align-items-center gap-1"><x-icon name="eye" class="w-3.5 h-3.5" /> {{ $post->views }}</span>
                            <span class="d-flex align-items-center gap-1"><x-icon name="heart" class="w-3.5 h-3.5" /> {{ $post->likes_count }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('blog.index') }}" class="d-inline-flex align-items-center gap-2 text-brand fw-medium hover-underline">
                View All Posts <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
