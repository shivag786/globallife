@php $categories = app(\App\Repositories\ProductRepository::class)->groupedByCategory(); @endphp
@if ($categories->isNotEmpty())
    <div class="mw-6xl mx-auto px-6 py-16">
        @if ($section->title || $section->subtitle)
            <div class="text-center mb-12 reveal">
                @if ($section->title)
                    <h2 class="font-display fs-3xl fw-bold text-brand-emphasis mb-2">{{ $section->title }}</h2>
                @endif
                @if ($section->subtitle)
                    <p class="text-secondary">{{ $section->subtitle }}</p>
                @endif
            </div>
        @endif

        <div class="d-flex flex-column gap-16">
            @foreach ($categories as $category)
                <div class="reveal">
                    <div class="d-flex align-items-end justify-content-between gap-4 mb-6">
                        <div>
                            <h3 class="font-display fs-2xl fw-semibold text-brand-emphasis">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="fs-sm text-secondary mt-1 mw-xl">{{ $category->description }}</p>
                            @endif
                        </div>
                        <a href="{{ route('products.category', $category) }}" class="d-none d-sm-inline-flex align-items-center gap-2 fs-sm text-brand fw-medium hover-underline text-nowrap">
                            View all <x-icon name="arrow-right" class="w-4 h-4" />
                        </a>
                    </div>

                    <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
                        @foreach ($category->products as $product)
                            <x-store-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
