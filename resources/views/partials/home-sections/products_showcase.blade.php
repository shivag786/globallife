@php $featuredProducts = app(\App\Repositories\ProductRepository::class)->featured(); @endphp
@if ($featuredProducts->isNotEmpty())
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-slate-500">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($featuredProducts as $index => $product)
                <a href="{{ route('products.show', $product) }}"
                   class="reveal group block bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition"
                   style="transition-delay: {{ $index * 0.08 }}s">
                    <div class="aspect-square bg-brand-50 overflow-hidden">
                        @if ($product->main_image)
                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @endif
                    </div>
                    <div class="p-5">
                        @if ($product->badge)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-gold-500/10 text-gold-600 px-2 py-1 rounded-full mb-2">
                                <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
                            </span>
                        @endif
                        <h3 class="font-display font-semibold text-brand-900">{{ $product->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $product->short_description }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-brand-700 font-medium hover:underline">
                View All Products <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
