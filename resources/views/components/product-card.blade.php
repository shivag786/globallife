@props(['product', 'index' => 0])

@php
    // Whole rupees stay clean (₹499); paise show when present (₹499.50).
    $money = fn ($n) => '₹' . number_format((float) $n, fmod((float) $n, 1) == 0.0 ? 0 : 2);
@endphp

<a href="{{ route('products.show', $product) }}"
   {{ $attributes->merge(['class' => 'reveal d-flex flex-column bg-body border rounded-4 overflow-hidden premium-shadow']) }}
   style="transition-delay: {{ ($index % 6) * 0.08 }}s">
    <div class="position-relative aspect-video overflow-hidden" style="background: linear-gradient(to bottom, var(--bs-body-bg), color-mix(in srgb, var(--color-brand-50) 60%, transparent))">
        @if ($product->main_image)
            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}"
                 class="w-100 h-100 object-fit-contain p-6">
        @endif
        @if ($product->discountPercentage())
            <span class="position-absolute top-3 end-3 fs-xs fw-semibold bg-success text-white px-2 py-1 rounded-pill shadow-sm">
                {{ $product->discountPercentage() }}% OFF
            </span>
        @endif
    </div>
    <div class="p-5 d-flex flex-column flex-grow-1">
        @if ($product->badge)
            <span class="d-inline-flex align-items-center gap-1 fs-xs fw-semibold bg-gold-subtle text-gold px-2 py-1 rounded-pill mb-2 align-self-start">
                <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
            </span>
        @endif
        <h3 class="font-display fs-base fw-semibold text-brand-emphasis">{{ $product->name }}</h3>
        <p class="fs-sm text-secondary mt-1 line-clamp-2">{{ $product->short_description }}</p>

        <div class="mt-auto pt-4">
            @if ($product->hasPrice())
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-lg fw-bold text-brand-emphasis">{{ $money($product->price) }}</span>
                    @if ($product->hasDiscount())
                        <span class="fs-sm text-secondary text-decoration-line-through">{{ $money($product->mrp) }}</span>
                    @endif
                </div>
            @else
                <span class="fs-sm fw-medium text-brand">Enquire for price</span>
            @endif
        </div>
    </div>
</a>
