<x-layouts.public title="Blog" :metaDescription="'Wellness tips, product guides, and updates from Global Life.'">
    <div class="max-w-5xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl font-bold text-brand-900 mb-3">From the Blog</h1>
            <p class="text-slate-500">Wellness tips, product guides, and company updates.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-8">
            @forelse ($posts as $index => $post)
                <a href="{{ route('blog.show', $post) }}"
                   class="reveal group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition"
                   style="transition-delay: {{ ($index % 6) * 0.08 }}s">
                    <div class="aspect-[16/9] bg-brand-50 overflow-hidden">
                        @if ($post->featured_image)
                            <img src="{{ asset('storage/'.$post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                        @endif
                    </div>
                    <div class="p-6">
                        @if ($post->category)
                            <span class="text-xs font-semibold text-brand-600 uppercase tracking-wide">{{ $post->category }}</span>
                        @endif
                        <h2 class="font-display text-lg font-semibold text-brand-900 mt-1 mb-2">{{ $post->title }}</h2>
                        <p class="text-sm text-slate-500">{{ $post->excerpt }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <p class="text-xs text-slate-400">{{ $post->published_at?->format('M j, Y') }}</p>
                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1"><x-icon name="eye" class="w-3.5 h-3.5" /> {{ $post->views }}</span>
                                <span class="flex items-center gap-1"><x-icon name="heart" class="w-3.5 h-3.5" /> {{ $post->likes_count }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-slate-400 py-16">No posts published yet.</p>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    </div>
</x-layouts.public>
