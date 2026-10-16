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

    <div class="mw-6xl mx-auto px-6 py-16">
        <a href="{{ route('products.index') }}" class="d-inline-flex align-items-center gap-1 fs-sm text-brand hover-underline">
            <x-icon name="arrow-right" class="w-4 h-4" /> All Products
        </a>

        <div class="d-grid grid-cols-md-2 gap-12 mt-6 align-items-start">
            {{-- Product image on a clean stage so the full bottle/pack is always visible --}}
            <div data-product-media class="reveal position-relative aspect-square rounded-4 overflow-hidden premium-shadow border" style="background: linear-gradient(to bottom, var(--bs-body-bg), color-mix(in srgb, var(--color-brand-50) 60%, transparent))">
                @if ($product->main_image)
                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-contain p-10">
                @endif
                @if ($product->discountPercentage())
                    <span class="position-absolute top-4 end-4 fs-xs fw-semibold bg-success text-white px-3 py-2 rounded-pill shadow-sm">
                        {{ $product->discountPercentage() }}% OFF
                    </span>
                @endif
            </div>

            <div class="reveal" style="transition-delay: 0.1s">
                @if ($product->badge)
                    <span class="d-inline-flex align-items-center gap-1 fs-xs fw-semibold text-gold px-2 py-1 rounded-pill mb-3" style="background-color: color-mix(in srgb, var(--color-gold-500) 10%, transparent)">
                        <x-icon name="sparkles" class="w-3.5 h-3.5" /> {{ $product->badge }}
                    </span>
                @endif
                @if ($product->category)
                    <p class="fs-xs text-uppercase tracking-wider text-secondary mb-1">{{ $product->category }}</p>
                @endif
                <h1 class="font-display fs-3xl fw-bold text-brand-emphasis mb-3">{{ $product->name }}</h1>
                <p class="text-body-secondary">{{ $product->short_description }}</p>

                {{-- Price block --}}
                @if ($product->hasPrice())
                    <div class="mt-6">
                        <div class="d-flex flex-wrap align-items-end gap-3">
                            <span class="fs-4xl fw-bold text-brand-emphasis">{{ $money($product->price) }}</span>
                            @if ($product->hasDiscount())
                                <span class="fs-xl text-secondary text-decoration-line-through mb-1">{{ $money($product->mrp) }}</span>
                                <span class="fs-sm fw-semibold text-success bg-success-subtle px-2 py-1 rounded-pill mb-2">{{ $product->discountPercentage() }}% OFF</span>
                            @endif
                        </div>
                        @if ($product->hasDiscount())
                            <p class="fs-sm text-success mt-2 fw-medium">You save {{ $money((float) $product->mrp - (float) $product->price) }}</p>
                        @endif
                        <p class="fs-xs text-secondary mt-1">Inclusive of all taxes</p>
                    </div>
                @else
                    <div class="mt-6">
                        <span class="fs-2xl fw-semibold text-brand-emphasis">Price on enquiry</span>
                    </div>
                @endif

                @if ($product->hasPrice())
                    @php $deliveryPromise = app(\App\Services\DeliveryService::class)->promiseDate(); @endphp
                    <div class="mt-5 d-inline-flex align-items-center gap-2 bg-brand-subtle border border-brand-subtle rounded-3 px-4 py-2">
                        <x-icon name="truck" class="w-5 h-5 text-brand flex-shrink-0" />
                        <span class="fs-sm text-body-secondary">Order today, delivery by
                            <span class="fw-semibold text-brand-emphasis">{{ $deliveryPromise->format('D, d M Y') }}</span>
                        </span>
                    </div>
                @endif

                @if ($product->tags)
                    <div class="d-flex flex-wrap gap-2 mt-6">
                        @foreach ($product->tags as $tag)
                            <span class="fs-xs bg-brand-subtle text-brand px-3 py-1 rounded-pill">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($product->hasPrice())
                    @if ($seller ?? null)
                        <p class="mt-6 fs-sm text-secondary">Sold by <span class="fw-medium text-brand">{{ $seller->business_name }}</span></p>
                    @endif
                    <div class="mt-3 d-flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('cart.add') }}" data-cart-ajax data-role="add">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @if ($seller ?? null) <input type="hidden" name="seller_id" value="{{ $seller->id }}"> @endif
                            <button type="submit" class="btn btn-brand rounded-pill d-inline-flex align-items-center justify-content-center gap-2 premium-shadow">
                                <x-icon name="shopping-bag" class="w-5 h-5" /> Add to Cart
                            </button>
                        </form>
                        <form method="POST" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="buy_now" value="1">
                            @if ($seller ?? null) <input type="hidden" name="seller_id" value="{{ $seller->id }}"> @endif
                            <button type="submit" class="btn btn-link p-0 text-decoration-none d-inline-flex align-items-center justify-content-center gap-2 border border-gold text-gold px-7 py-3 rounded-pill fw-medium">Buy Now</button>
                        </form>
                    </div>
                @endif

                {{-- Secondary CTAs — enquiry lead pipeline + WhatsApp --}}
                <div class="mt-4 d-flex flex-wrap gap-3">
                    <a href="#enquire" class="d-inline-flex align-items-center justify-content-center gap-2 {{ $product->hasPrice() ? 'border border-brand text-brand' : 'bg-brand text-white premium-shadow' }} px-7 py-3 rounded-pill fw-medium">
                        <x-icon name="chat-bubble" class="w-5 h-5" /> {{ $ctaLabel }}
                    </a>
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}?text={{ $waText }}" target="_blank" rel="noopener"
                           class="d-inline-flex align-items-center justify-content-center gap-2 border border-success text-success px-7 py-3 rounded-pill fw-medium">
                            <x-icon name="chat-bubble" class="w-5 h-5" /> Order on WhatsApp
                        </a>
                    @endif
                    <x-product-benefits :product="$product" class="px-7 py-3" />
                </div>

                {{-- Trust strip --}}
                <div class="mt-8 d-grid grid-cols-3 gap-4 border-top pt-6">
                    <div class="d-flex flex-column align-items-center text-center gap-2">
                        <x-icon name="shield-check" class="w-6 h-6 text-brand" />
                        <span class="fs-xs text-secondary">100% Authentic</span>
                    </div>
                    <div class="d-flex flex-column align-items-center text-center gap-2">
                        <x-icon name="check-circle" class="w-6 h-6 text-brand" />
                        <span class="fs-xs text-secondary">Lab Tested</span>
                    </div>
                    <div class="d-flex flex-column align-items-center text-center gap-2">
                        <x-icon name="truck" class="w-6 h-6 text-brand" />
                        <span class="fs-xs text-secondary">Pan-India Delivery</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Specs + About --}}
        <div class="d-grid grid-cols-md-2 gap-12 mt-16">
            @if ($product->specs)
                <div class="reveal">
                    <h2 class="font-display fs-xl fw-bold text-brand-emphasis mb-4">Specifications</h2>
                    <table class="w-100 fs-sm border-top">
                        @foreach ($product->specs as $key => $value)
                            <tr class="border-bottom">
                                <td class="py-2 text-secondary">{{ $key }}</td>
                                <td class="py-2 text-body-secondary fw-medium">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

            @if ($product->long_description)
                <div class="reveal" style="transition-delay: 0.1s">
                    <h2 class="font-display fs-xl fw-bold text-brand-emphasis mb-4">About this product</h2>
                    <div class="editor-content text-body-secondary">{!! $product->long_description !!}</div>
                </div>
            @endif
        </div>

        {{-- Enquiry / order section --}}
        <div id="enquire" class="mt-16 scroll-offset bg-body border rounded-4 premium-shadow p-8 mw-3xl mx-auto">
            <h2 class="font-display fs-2xl fw-bold text-brand-emphasis mb-1">{{ $ctaLabel }}</h2>
            <p class="fs-sm text-secondary mb-6">Share your details and a city manager will confirm availability, pricing, and delivery.</p>
            @include('partials.enquiry-form', ['source' => 'product', 'prefillMessage' => $enquiryMessage])
        </div>

        {{-- Related products --}}
        @if ($related->isNotEmpty())
            <div class="mt-20">
                <h2 class="font-display fs-2xl fw-bold text-brand-emphasis mb-8 text-center">You may also like</h2>
                <div class="d-grid grid-cols-sm-2 grid-cols-lg-4 gap-6">
                    @foreach ($related as $index => $item)
                        <x-product-card :product="$item" :index="$index" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.public>
