@php $categories = app(\App\Repositories\ProductRepository::class)->groupedByCategory(); @endphp
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

        <div class="space-y-16">
            @foreach ($categories as $category)
                <div class="reveal">
                    <div class="flex items-end justify-between gap-4 mb-6">
                        <div>
                            <h3 class="font-display text-2xl font-semibold text-brand-900">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="text-sm text-slate-500 mt-1 max-w-xl">{{ $category->description }}</p>
                            @endif
                        </div>
                        <a href="{{ route('products.category', $category) }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm text-brand-700 font-medium hover:underline whitespace-nowrap">
                            View all <x-icon name="arrow-right" class="w-4 h-4" />
                        </a>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($category->products as $product)
                            <x-store-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
