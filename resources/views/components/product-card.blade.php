@props(['product', 'index' => 0])

@php
    // Whole rupees stay clean (₹499); paise show when present (₹499.50).
    $money = fn ($n) => '₹' . number_format((float) $n, fmod((float) $n, 1) == 0.0 ? 0 : 2);
@endphp

<a href="{{ route('products.show', $product) }}"
   {{ $attributes->merge(['class' => 'reveal group flex flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden premium-shadow hover:-translate-y-1 transition']) }}
   style="transition-delay: {{ ($index % 6) * 0.08 }}s">
    <div class="relative aspect-[4/3] bg-gradient-to-b from-white to-brand-50/60 overflow-hidden">
        @if ($product->main_image)
            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}"
                 class="w-full h-full object-contain p-6 group-hover:scale-105 transition duration-500">
        @endif
        @if ($product->discountPercentage())
            <span class="absolute top-3 right-3 text-xs font-semibold bg-green-600 text-white px-2 py-1 rounded-full shadow-sm">
                {{ $product->discountPercentage() }}% OFF
            </span>
        @endif
    </div>
    <div class="p-5 flex flex-col flex-1">
        @if ($product->badge)
            <span class="inline-flex items-center gap-1 text-xs font-semibold bg-gold-500/10 text-gold-600 px-2 py-1 rounded-full mb-2 self-start">
                <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
            </span>
        @endif
        <h3 class="font-display text-base font-semibold text-brand-900">{{ $product->name }}</h3>
        <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $product->short_description }}</p>

        <div class="mt-auto pt-4">
            @if ($product->hasPrice())
                <div class="flex items-baseline gap-2">
                    <span class="text-lg font-bold text-brand-900">{{ $money($product->price) }}</span>
                    @if ($product->hasDiscount())
                        <span class="text-sm text-slate-400 line-through">{{ $money($product->mrp) }}</span>
                    @endif
                </div>
            @else
                <span class="text-sm font-medium text-brand-700">Enquire for price</span>
            @endif
        </div>
    </div>
</a>
