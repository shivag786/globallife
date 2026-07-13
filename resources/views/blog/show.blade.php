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

    <article class="max-w-3xl mx-auto px-6 py-16">
        <a href="{{ route('blog.index') }}" class="text-sm text-brand-700 hover:underline">&larr; Back to Blog</a>

        @if ($post->category)
            <p class="text-xs font-semibold text-brand-600 uppercase tracking-wide mt-6">{{ $post->category }}</p>
        @endif
        <h1 class="font-display text-3xl md:text-4xl font-bold text-brand-900 mt-2 mb-4">{{ $post->title }}</h1>
        <p class="text-sm text-slate-400 mb-8 flex flex-wrap items-center gap-x-2">
            <span>{{ $post->author?->name ?? config('app.name') }}</span> &middot;
            <span>{{ $post->published_at?->format('F j, Y') }}</span> &middot;
            <span class="flex items-center gap-1"><x-icon name="eye" class="w-4 h-4" /> {{ $post->views }} views</span> &middot;
            <span class="flex items-center gap-1"><x-icon name="heart" class="w-4 h-4" /> {{ $likesCount }} likes</span>
        </p>

        @if ($post->featured_image)
            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-2xl premium-shadow mb-10">
        @endif

        <div class="editor-content">{!! $post->content !!}</div>

        @if ($post->tags)
            <div class="flex flex-wrap gap-2 mt-10">
                @foreach ($post->tags as $tag)
                    <span class="text-xs bg-brand-50 text-brand-700 px-3 py-1 rounded-full">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        {{-- Like + Share --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mt-10 pt-6 border-t border-slate-100">
            <button type="button" id="blog-like-button" data-like-url="{{ route('blog.like', $post) }}"
                    data-liked="{{ $hasLiked ? '1' : '0' }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-sm font-medium transition {{ $hasLiked ? 'bg-rose-50 border-rose-200 text-rose-600' : 'border-slate-200 text-slate-600 hover:border-rose-200 hover:text-rose-600' }}">
                <x-icon name="heart" class="w-4 h-4" :filled="$hasLiked" id="blog-like-icon" />
                <span id="blog-like-count">{{ $likesCount }}</span> Like{{ $likesCount === 1 ? '' : 's' }}
            </button>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 flex items-center gap-1"><x-icon name="share" class="w-4 h-4" /> Share:</span>
                @php $shareUrl = urlencode(url()->current()); $shareText = urlencode($post->title); @endphp
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-semibold hover:bg-brand-100">FB</a>
                <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-semibold hover:bg-brand-100">X</a>
                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-semibold hover:bg-brand-100">WA</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-semibold hover:bg-brand-100">IN</a>
                <button type="button" data-copy-link="{{ url()->current() }}"
                        class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center hover:bg-brand-100" title="Copy link">
                    <x-icon name="share" class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Comments --}}
        <div id="comments" class="mt-12 pt-8 border-t border-slate-100">
            <h2 class="font-display text-2xl font-bold text-brand-900 mb-6">{{ $post->comments->count() }} Comment{{ $post->comments->count() === 1 ? '' : 's' }}</h2>

            @if (session('status'))
                <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6 mb-10">
                @forelse ($post->comments as $comment)
                    <div class="flex gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold flex-shrink-0">
                            {{ mb_substr($comment->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-brand-900">{{ $comment->name }} <span class="text-xs text-slate-400 font-normal">&middot; {{ $comment->created_at->diffForHumans() }}</span></p>
                            <p class="text-sm text-slate-600">{{ $comment->content }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Be the first to comment.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="space-y-4 max-w-lg">
                @csrf
                <div class="hidden" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="comment-name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input id="comment-name" type="text" name="name" value="{{ old('name') }}" required
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label for="comment-email" class="block text-sm font-medium text-slate-700">Email <span class="text-slate-400">(optional)</span></label>
                        <input id="comment-email" type="email" name="email" value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </div>
                <div>
                    <label for="comment-content" class="block text-sm font-medium text-slate-700">Comment</label>
                    <textarea id="comment-content" name="content" rows="3" required
                              class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('content') }}</textarea>
                </div>
                <button type="submit" class="bg-brand-700 text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-brand-800 transition">
                    Post Comment
                </button>
            </form>
        </div>
    </article>

    @if ($relatedPosts->isNotEmpty())
        <div class="bg-cream-dark/50 py-16 mt-16">
            <div class="max-w-5xl mx-auto px-6">
                <h2 class="font-display text-2xl font-bold text-brand-900 mb-8">More from the Blog</h2>
                <div class="grid sm:grid-cols-3 gap-6">
                    @foreach ($relatedPosts as $related)
                        <a href="{{ route('blog.show', $related) }}" class="group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition">
                            <div class="aspect-[16/9] bg-brand-50 overflow-hidden">
                                @if ($related->featured_image)
                                    <img src="{{ asset('storage/'.$related->featured_image) }}" alt="{{ $related->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-display font-semibold text-brand-900 text-sm">{{ $related->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.public>
