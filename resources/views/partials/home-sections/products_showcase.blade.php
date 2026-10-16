@php $featuredProducts = app(\App\Repositories\ProductRepository::class)->featured(); @endphp
@if ($featuredProducts->isNotEmpty())
    <div class="mw-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12 reveal">
            <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
            @if ($section->subtitle)
                <p class="text-secondary">{{ $section->subtitle }}</p>
            @endif
        </div>
        <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
            @foreach ($featuredProducts as $index => $product)
                <x-product-card :product="$product" :index="$index" />
            @endforeach
        </div>
        <div class="text-center mt-10 reveal">
            <a href="{{ route('products.index') }}" class="d-inline-flex align-items-center gap-2 text-brand fw-medium hover-underline">
                View All Products <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>
    </div>
@endif
