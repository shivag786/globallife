@php $categories = app(\App\Repositories\ProductRepository::class)->activeCategoriesWithProducts(); @endphp
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

        <div class="d-grid grid-cols-2 grid-cols-sm-3 grid-cols-lg-4 gap-5 gap-sm-6">
            @foreach ($categories as $index => $category)
                <a href="{{ route('products.category', $category) }}"
                   class="reveal position-relative d-block rounded-4 overflow-hidden premium-shadow bg-body border"
                   style="transition-delay: {{ ($index % 4) * 0.08 }}s">
                    <div class="aspect-square overflow-hidden" style="background: linear-gradient(to bottom, var(--bs-body-bg), color-mix(in srgb, var(--color-brand-50) 60%, transparent))">
                        @if ($category->image)
                            <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                                 class="w-100 h-100 object-fit-cover">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <x-icon name="tag" class="w-10 h-10 text-brand-" />
                            </div>
                        @endif
                    </div>
                    <div class="position-absolute start-0 end-0 bottom-0 p-4 pt-10" style="background: linear-gradient(to top, color-mix(in srgb, var(--color-brand-950) 80%, transparent), color-mix(in srgb, var(--color-brand-950) 30%, transparent), transparent)">
                        <p class="font-display fw-semibold text-white lh-sm">{{ $category->name }}</p>
                        <p class="fs-xs text-white text-opacity-75 mt-1">{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
