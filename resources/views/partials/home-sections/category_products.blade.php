@php $categories = app(\App\Repositories\ProductRepository::class)->activeCategoriesWithProducts(); @endphp
@if ($categories->isNotEmpty())
    <div class="max-w-6xl mx-auto px-6 py-16">
        @if ($section->title || $section->subtitle)
            <div class="text-center mb-12 reveal">
                @if ($section->title)
                    <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
                @endif
                @if ($section->subtitle)
                    <p class="text-slate-500">{{ $section->subtitle }}</p>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5 sm:gap-6">
            @foreach ($categories as $index => $category)
                <a href="{{ route('products.category', $category) }}"
                   class="reveal group relative block rounded-2xl overflow-hidden premium-shadow bg-white border border-slate-100 hover:-translate-y-1 transition"
                   style="transition-delay: {{ ($index % 4) * 0.08 }}s">
                    <div class="aspect-square bg-gradient-to-b from-white to-brand-50/60 overflow-hidden">
                        @if ($category->image)
                            <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <x-icon name="tag" class="w-10 h-10 text-brand-300" />
                            </div>
                        @endif
                    </div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-brand-950/80 via-brand-950/30 to-transparent p-4 pt-10">
                        <p class="font-display font-semibold text-white leading-tight">{{ $category->name }}</p>
                        <p class="text-xs text-white/80 mt-0.5">{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
