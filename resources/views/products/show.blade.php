<x-layouts.public
    :title="$product->meta_title ?: $product->name"
    :metaDescription="$product->meta_description ?: $product->short_description"
    :canonical="$product->canonical_url ?: url()->current()"
    :ogImage="$product->main_image ? asset('storage/'.$product->main_image) : null"
>
    @php
        $settings = app(\App\Services\SettingsService::class)->all();
        $whatsapp = ! empty($settings['contact_whatsapp']) ? preg_replace('/\D/', '', $settings['contact_whatsapp']) : null;

        // Whole rupees stay clean (₹499); paise show only when present (₹499.50).
        $money = fn ($n) => '₹' . number_format((float) $n, fmod((float) $n, 1) == 0.0 ? 0 : 2);

        $ctaLabel = $product->hasPrice() ? 'Enquire to Order' : 'Enquire for Price';
        $waText = rawurlencode(
            "Hi, I'm interested in ordering: {$product->name}"
            . ($product->hasPrice() ? ' (' . $money($product->price) . ')' : '')
            . '. Please share the details.'
        );
        $enquiryMessage = "I'm interested in ordering: {$product->name}."
            . ($product->hasPrice() ? ' (Listed price ' . $money($product->price) . ')' : '');
    @endphp

    <div class="max-w-6xl mx-auto px-6 py-16">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
            <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> All Products
        </a>

        <div class="grid md:grid-cols-2 gap-12 mt-6 items-start">
            {{-- Product image on a clean stage so the full bottle/pack is always visible --}}
            <div class="reveal relative aspect-square bg-gradient-to-b from-white to-brand-50/60 rounded-2xl overflow-hidden premium-shadow border border-slate-100">
                @if ($product->main_image)
                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-10">
                @endif
                @if ($product->discountPercentage())
                    <span class="absolute top-4 right-4 text-xs font-semibold bg-green-600 text-white px-3 py-1.5 rounded-full shadow-sm">
                        {{ $product->discountPercentage() }}% OFF
                    </span>
                @endif
            </div>

            <div class="reveal" style="transition-delay: 0.1s">
                @if ($product->badge)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-gold-500/10 text-gold-600 px-2 py-1 rounded-full mb-3">
                        <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
                    </span>
                @endif
                @if ($product->category)
                    <p class="text-xs uppercase tracking-wider text-slate-400 mb-1">{{ $product->category }}</p>
                @endif
                <h1 class="font-display text-3xl font-bold text-brand-900 mb-3">{{ $product->name }}</h1>
                <p class="text-slate-600">{{ $product->short_description }}</p>

                {{-- Price block --}}
                @if ($product->hasPrice())
                    <div class="mt-6">
                        <div class="flex flex-wrap items-end gap-3">
                            <span class="text-4xl font-bold text-brand-900">{{ $money($product->price) }}</span>
                            @if ($product->hasDiscount())
                                <span class="text-xl text-slate-400 line-through mb-1">{{ $money($product->mrp) }}</span>
                                <span class="text-sm font-semibold text-green-700 bg-green-50 px-2.5 py-1 rounded-full mb-1.5">{{ $product->discountPercentage() }}% OFF</span>
                            @endif
                        </div>
                        @if ($product->hasDiscount())
                            <p class="text-sm text-green-700 mt-1.5 font-medium">You save {{ $money((float) $product->mrp - (float) $product->price) }}</p>
                        @endif
                        <p class="text-xs text-slate-400 mt-1">Inclusive of all taxes</p>
                    </div>
                @else
                    <div class="mt-6">
                        <span class="text-2xl font-semibold text-brand-800">Price on enquiry</span>
                    </div>
                @endif

                @if ($product->tags)
                    <div class="flex flex-wrap gap-2 mt-6">
                        @foreach ($product->tags as $tag)
                            <span class="text-xs bg-brand-50 text-brand-700 px-3 py-1 rounded-full">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- CTAs — route through the existing enquiry lead pipeline (no payment gateway) --}}
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#enquire" class="inline-flex items-center justify-center gap-2 bg-brand-700 text-white px-7 py-3.5 rounded-full font-medium hover:bg-brand-800 transition premium-shadow">
                        <x-icon name="chat-bubble" class="w-5 h-5" /> {{ $ctaLabel }}
                    </a>
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}?text={{ $waText }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 border border-green-600 text-green-700 px-7 py-3.5 rounded-full font-medium hover:bg-green-50 transition">
                            <x-icon name="chat-bubble" class="w-5 h-5" /> Order on WhatsApp
                        </a>
                    @endif
                </div>

                {{-- Trust strip --}}
                <div class="mt-8 grid grid-cols-3 gap-4 border-t border-slate-100 pt-6">
                    <div class="flex flex-col items-center text-center gap-1.5">
                        <x-icon name="shield-check" class="w-6 h-6 text-brand-700" />
                        <span class="text-xs text-slate-500">100% Authentic</span>
                    </div>
                    <div class="flex flex-col items-center text-center gap-1.5">
                        <x-icon name="check-circle" class="w-6 h-6 text-brand-700" />
                        <span class="text-xs text-slate-500">Lab Tested</span>
                    </div>
                    <div class="flex flex-col items-center text-center gap-1.5">
                        <x-icon name="truck" class="w-6 h-6 text-brand-700" />
                        <span class="text-xs text-slate-500">Pan-India Delivery</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Specs + About --}}
        <div class="grid md:grid-cols-2 gap-12 mt-16">
            @if ($product->specs)
                <div class="reveal">
                    <h2 class="font-display text-xl font-bold text-brand-900 mb-4">Specifications</h2>
                    <table class="w-full text-sm border-t border-slate-100">
                        @foreach ($product->specs as $key => $value)
                            <tr class="border-b border-slate-100">
                                <td class="py-2.5 text-slate-400 w-1/3">{{ $key }}</td>
                                <td class="py-2.5 text-slate-700 font-medium">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

            @if ($product->long_description)
                <div class="reveal" style="transition-delay: 0.1s">
                    <h2 class="font-display text-xl font-bold text-brand-900 mb-4">About this product</h2>
                    <div class="editor-content text-slate-600">{!! $product->long_description !!}</div>
                </div>
            @endif
        </div>

        {{-- Enquiry / order section --}}
        <div id="enquire" class="mt-16 scroll-mt-24 bg-white border border-slate-100 rounded-2xl premium-shadow p-8 max-w-3xl mx-auto">
            <h2 class="font-display text-2xl font-bold text-brand-900 mb-1">{{ $ctaLabel }}</h2>
            <p class="text-sm text-slate-500 mb-6">Share your details and a city manager will confirm availability, pricing, and delivery.</p>
            @include('partials.enquiry-form', ['source' => 'product', 'prefillMessage' => $enquiryMessage])
        </div>

        {{-- Related products --}}
        @if ($related->isNotEmpty())
            <div class="mt-20">
                <h2 class="font-display text-2xl font-bold text-brand-900 mb-8 text-center">You may also like</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($related as $index => $item)
                        <x-product-card :product="$item" :index="$index" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.public>
