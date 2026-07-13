@php $posts = app(\App\Repositories\BlogPostRepository::class)->latestPublished(3); @endphp
@if ($posts->isNotEmpty())
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $index => $post)
                <a href="{{ route('blog.show', $post) }}"
                   class="reveal group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition"
                   style="transition-delay: {{ $index * 0.1 }}s">
                    <div class="aspect-[16/9] bg-brand-50 overflow-hidden">
                        @if ($post->featured_image)
                            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-display font-semibold text-brand-900 mb-2">{{ $post->title }}</h3>
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span class="flex items-center gap-1"><x-icon name="eye" class="w-3.5 h-3.5" /> {{ $post->views }}</span>
                            <span class="flex items-center gap-1"><x-icon name="heart" class="w-3.5 h-3.5" /> {{ $post->likes_count }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1.5 text-brand-700 font-medium hover:underline">
                View All Posts <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
