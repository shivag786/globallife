<x-layouts.public
    :title="$product->meta_title ?: $product->name"
    :metaDescription="$product->meta_description ?: $product->short_description"
    :canonical="$product->canonical_url ?: url()->current()"
    :ogImage="$product->main_image ? asset('storage/'.$product->main_image) : null"
>
    <div class="max-w-5xl mx-auto px-6 py-16">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
            <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> All Products
        </a>

        <div class="grid md:grid-cols-2 gap-12 mt-6">
            <div class="reveal aspect-square bg-brand-50 rounded-2xl overflow-hidden premium-shadow">
                @if ($product->main_image)
                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @endif
            </div>

            <div class="reveal" style="transition-delay: 0.1s">
                @if ($product->badge)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-gold-500/10 text-gold-600 px-2 py-1 rounded-full mb-3">
                        <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
                    </span>
                @endif
                <h1 class="font-display text-3xl font-bold text-brand-900 mb-2">{{ $product->name }}</h1>
                @if ($product->category)
                    <p class="text-sm text-slate-400 mb-4">{{ $product->category }}</p>
                @endif
                <p class="text-slate-600 mb-6">{{ $product->short_description }}</p>

                @if ($product->tags)
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach ($product->tags as $tag)
                            <span class="text-xs bg-brand-50 text-brand-700 px-3 py-1 rounded-full">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($product->specs)
                    <table class="w-full text-sm border-t border-slate-100">
                        @foreach ($product->specs as $key => $value)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 text-slate-400 w-1/3">{{ $key }}</td>
                                <td class="py-2 text-slate-700 font-medium">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </div>

        @if ($product->long_description)
            <div class="mt-12 max-w-3xl">
                <h2 class="font-display text-2xl font-bold text-brand-900 mb-4">About this product</h2>
                <div class="editor-content">{!! $product->long_description !!}</div>
            </div>
        @endif
    </div>
</x-layouts.public>
