<x-layouts.public
    :title="$post->meta_title ?: $post->title"
    :metaDescription="$post->meta_description ?: $post->excerpt"
    :canonical="$post->canonical_url ?: url()->current()"
    :ogImage="$post->featured_image ? asset('storage/'.$post->featured_image) : null"
>
    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->title,
                'description' => $post->meta_description ?: $post->excerpt,
                'image' => $post->featured_image ? asset('storage/'.$post->featured_image) : null,
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => $post->updated_at->toIso8601String(),
                'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? config('app.name')],
                'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
            ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot:head>

    <article class="mw-3xl mx-auto px-6 py-16">
        <a href="{{ route('blog.index') }}" class="fs-sm text-brand hover-underline">&larr; Back to Blog</a>

        @if ($post->category)
            <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mt-6">{{ $post->category }}</p>
        @endif
        <h1 class="font-display fs-3xl fs-4xl fw-bold text-brand-emphasis mt-2 mb-4">{{ $post->title }}</h1>
        <p class="fs-sm text-secondary mb-8 d-flex flex-wrap align-items-center column-gap-2">
            <span>{{ $post->author?->name ?? config('app.name') }}</span> &middot;
            <span>{{ $post->published_at?->format('F j, Y') }}</span> &middot;
            <span class="d-flex align-items-center gap-1"><x-icon name="eye" class="w-4 h-4" /> {{ $post->views }} views</span> &middot;
            <span class="d-flex align-items-center gap-1"><x-icon name="heart" class="w-4 h-4" /> {{ $likesCount }} likes</span>
        </p>

        @if ($post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-100 rounded-4 premium-shadow mb-10">
        @endif

        <div class="editor-content">{!! $post->content !!}</div>

        @if ($post->tags)
            <div class="d-flex flex-wrap gap-2 mt-10">
                @foreach ($post->tags as $tag)
                    <span class="fs-xs bg-brand-subtle text-brand px-3 py-1 rounded-pill">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        {{-- Like + Share --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mt-10 pt-6 border-top">
            <button type="button" id="blog-like-button" data-like-url="{{ route('blog.like', $post) }}"
                    data-liked="{{ $hasLiked ? '1' : '0' }}"
                    class="d-inline-flex align-items-center gap-2 px-4 py-2 rounded-pill border fs-sm fw-medium {{ $hasLiked ? 'bg-danger-subtle border-danger-subtle text-danger' : 'text-body-secondary' }}">
                <x-icon name="heart" class="w-4 h-4" :filled="$hasLiked" id="blog-like-icon" />
                <span id="blog-like-count">{{ $likesCount }}</span> Like{{ $likesCount === 1 ? '' : 's' }}
            </button>

            <div class="d-flex align-items-center gap-2">
                <span class="fs-xs text-secondary d-flex align-items-center gap-1"><x-icon name="share" class="w-4 h-4" /> Share:</span>
                @php $shareUrl = urlencode(url()->current()); $shareText = urlencode($post->title); @endphp
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center fs-xs fw-semibold">FB</a>
                <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center fs-xs fw-semibold">X</a>
                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center fs-xs fw-semibold">WA</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center fs-xs fw-semibold">IN</a>
                <button type="button" data-copy-link="{{ url()->current() }}"
                        class="btn btn-link p-0 text-decoration-none w-8 h-8 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center" title="Copy link">
                    <x-icon name="share" class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Comments --}}
        <div id="comments" class="mt-12 pt-8 border-top">
            <h2 class="font-display fs-2xl fw-bold text-brand-emphasis mb-6">{{ $post->comments->count() }} Comment{{ $post->comments->count() === 1 ? '' : 's' }}</h2>

            @if (session('status'))
                <div class="mb-4 fs-sm text-success bg-success-subtle border border-success-subtle rounded p-3">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded p-3">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex flex-column gap-6 mb-10">
                @forelse ($post->comments as $comment)
                    <div class="d-flex gap-3">
                        <div class="w-9 h-9 rounded-pill bg-brand-subtle text-brand d-flex align-items-center justify-content-center fs-sm fw-semibold flex-shrink-0">
                            {{ mb_substr($comment->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="fs-sm fw-semibold text-brand-emphasis">{{ $comment->name }} <span class="fs-xs text-secondary fw-normal">&middot; {{ $comment->created_at->diffForHumans() }}</span></p>
                            <p class="fs-sm text-body-secondary">{{ $comment->content }}</p>
                        </div>
                    </div>
                @empty
                    <p class="fs-sm text-secondary">Be the first to comment.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="d-flex flex-column gap-4 mw-lg">
                @csrf
                <div class="d-none" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="d-grid grid-cols-sm-2 gap-4">
                    <div>
                        <label for="comment-name" class="d-block fs-sm fw-medium text-body-secondary">Name</label>
                        <input id="comment-name" type="text" name="name" value="{{ old('name') }}" required
                               class="mt-1 d-block w-100 rounded-2 shadow-sm">
                    </div>
                    <div>
                        <label for="comment-email" class="d-block fs-sm fw-medium text-body-secondary">Email <span class="text-secondary">(optional)</span></label>
                        <input id="comment-email" type="email" name="email" value="{{ old('email') }}"
                               class="mt-1 d-block w-100 rounded-2 shadow-sm">
                    </div>
                </div>
                <div>
                    <label for="comment-content" class="d-block fs-sm fw-medium text-body-secondary">Comment</label>
                    <textarea id="comment-content" name="content" rows="3" required
                              class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('content') }}</textarea>
                </div>
                <button type="submit" class="btn btn-brand btn-sm rounded-pill">
                    Post Comment
                </button>
            </form>
        </div>
    </article>

    @if ($relatedPosts->isNotEmpty())
        <div class="bg-cream py-16 mt-16">
            <div class="mw-5xl mx-auto px-6">
                <h2 class="font-display fs-2xl fw-bold text-brand-emphasis mb-8">More from the Blog</h2>
                <div class="d-grid grid-cols-sm-3 gap-6">
                    @foreach ($relatedPosts as $related)
                        <a href="{{ route('blog.show', $related) }}" class="d-block bg-body border rounded-4 overflow-hidden premium-shadow">
                            <div class="aspect-video bg-brand-subtle overflow-hidden">
                                @if ($related->featured_image)
                                    <img src="{{ asset('storage/'.$related->featured_image) }}" alt="{{ $related->title }}" class="w-100 h-100 object-fit-cover">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-display fw-semibold text-brand-emphasis fs-sm">{{ $related->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.public>
