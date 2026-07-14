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
                <x-product-card :product="$product" :index="$index" />
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-brand-700 font-medium hover:underline">
                View All Products <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
