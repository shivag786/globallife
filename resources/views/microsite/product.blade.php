@php
    // One of a VIP business's OWN products (BusinessProduct), on its own page.
    // Buying follows the package exactly as the card does (VipPlan::selling_mode):
    // direct → company checkout; catalogue → order on WhatsApp.
    $money = fn ($n) => '₹'.number_format((float) $n, 0);
    $direct = $microsite->sellsDirect();
    $canBuy = $direct && $product->isPurchasable();
    $showPrice = $product->show_pricing && $product->hasPrice();
    $whatsapp = $direct ? null : $microsite->whatsappOrderUrl($product);
    $inWishlist = app(\App\Services\WishlistService::class)->hasBusiness($product->id);
    $homeUrl = url($microsite->publicPath());

    $specs = array_filter([
        'Brand' => $product->brand,
        'SKU' => $product->sku,
        'Weight' => $product->weight,
        'Colour' => $product->color,
        'Variant' => $product->variant,
    ], fn ($value) => filled($value));
    $tags = array_filter((array) $product->tags);

    $navSections = array_filter([
        'products' => 'Products',
        'services' => $microsite->isModuleVisible('services') ? 'Services' : null,
        'enquiry' => 'Contact',
    ]);
@endphp
<x-layouts.microsite :title="($product->meta_title ?: $product->name).' · '.$microsite->business_name" :microsite="$microsite"
                     :nav-sections="$navSections" :meta-description="$product->meta_description ?: $product->short_description"
                     :og-image="$product->image_path">
    <section class="msite-section pt-6 pt-md-10">
        <div class="msite-container">
            <nav aria-label="breadcrumb" class="fs-sm mb-6">
                <ol class="breadcrumb mb-0 flex-nowrap text-truncate">
                    <li class="breadcrumb-item"><a href="{{ $homeUrl }}" class="text-brand text-decoration-none">{{ $microsite->business_name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ $homeUrl }}#products" class="text-brand text-decoration-none">Products</a></li>
                    <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            <div class="row g-5 g-lg-10 align-items-start">
                <div class="col-12 col-md-6">
                    <div class="msite-card overflow-hidden position-relative aspect-square bg-brand-subtle">
                        @if ($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                        @else
                            <span class="w-100 h-100 d-flex align-items-center justify-content-center">
                                <x-icon name="tag" class="w-16 h-16 text-brand" />
                            </span>
                        @endif
                        @if ($showPrice && $product->discountPercentage())
                            <span class="position-absolute top-3 start-3 bg-gold text-brand-emphasis fs-xs fw-bold px-3 py-1 rounded-pill">
                                {{ $product->discountPercentage() }}% OFF
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    @if ($product->category)
                        <p class="fs-xs fw-semibold text-brand text-uppercase tracking-wide mb-2">{{ $product->category }}</p>
                    @endif
                    <h1 class="msite-heading fs-3xl mb-3 text-break">{{ $product->name }}</h1>

                    @if ($showPrice)
                        <div class="d-flex flex-wrap align-items-baseline gap-2 mb-3">
                            <span class="fs-2xl fw-bold text-brand">{{ $money($product->sellingPrice()) }}</span>
                            @if ($product->strikeThroughPrice())
                                <span class="fs-lg text-secondary text-decoration-line-through">{{ $money($product->strikeThroughPrice()) }}</span>
                                <span class="fs-sm fw-semibold text-success">Save {{ $product->discountPercentage() }}%</span>
                            @endif
                        </div>
                    @endif

                    <p class="fs-sm fw-semibold mb-4 {{ $product->isInStock() ? 'text-success' : 'text-danger' }}">
                        {{ $product->isInStock() ? 'In stock' : 'Out of stock' }}
                    </p>

                    @if ($product->short_description)
                        <p class="fs-lg text-body-secondary mb-5">{{ $product->short_description }}</p>
                    @endif

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
                        @if ($canBuy)
                            <form method="POST" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="business_product_id" value="{{ $product->id }}">
                                <button type="submit" class="msite-btn msite-btn-primary">
                                    <x-icon name="shopping-bag" class="w-4 h-4" /> Add to cart
                                </button>
                            </form>
                            <form method="POST" action="{{ route('cart.add') }}">
                                @csrf
                                <input type="hidden" name="business_product_id" value="{{ $product->id }}">
                                <input type="hidden" name="buy_now" value="1">
                                <button type="submit" class="msite-btn border border-2 border-brand text-brand bg-transparent">Buy Now</button>
                            </form>
                        @elseif ($direct)
                            <span class="btn rounded-pill border text-secondary disabled">
                                {{ $product->isInStock() ? 'Not available online' : 'Out of stock' }}
                            </span>
                        @elseif ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="msite-btn msite-btn-whatsapp">
                                <x-whatsapp-icon class="w-5 h-5" /> Order on WhatsApp
                            </a>
                        @else
                            <a href="#enquiry" class="msite-btn msite-btn-primary">Enquire to order</a>
                        @endif

                        @include('partials.microsite.wishlist-toggle', ['product' => $product, 'inWishlist' => $inWishlist, 'class' => ''])
                    </div>

                    @if ($specs)
                        <dl class="row fs-sm mb-4 g-0 border-top pt-4">
                            @foreach ($specs as $label => $value)
                                <dt class="col-4 col-sm-3 text-secondary fw-medium mb-2">{{ $label }}</dt>
                                <dd class="col-8 col-sm-9 mb-2 text-break">{{ $value }}</dd>
                            @endforeach
                        </dl>
                    @endif

                    @if ($tags)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <span class="fs-xs px-3 py-1 rounded-pill bg-brand-subtle text-brand">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($product->long_description)
                <div class="msite-card p-6 p-md-8 mt-10">
                    <h2 class="msite-heading fs-xl mb-4">Product details</h2>
                    <div class="text-body-secondary text-prewrap text-break">{{ $product->long_description }}</div>
                </div>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="msite-section bg-cream">
            <div class="msite-container">
                <h2 class="msite-heading fs-2xl mb-6">More from {{ $microsite->business_name }}</h2>
                <div class="d-grid grid-cols-2 grid-cols-lg-4 gap-4 gap-md-6">
                    @foreach ($related as $other)
                        <x-microsite.business-product-card :product="$other" :microsite="$microsite" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="enquiry" class="msite-section pt-0">
        <div class="msite-container mw-lg">
            <div class="msite-card p-6 p-md-8">
                <h2 class="msite-heading fs-2xl mb-5">Ask about this product</h2>
                @include('partials.enquiry-form', [
                    'source' => 'microsite',
                    'vipMicrositeId' => $microsite->id,
                    'prefillCity' => $microsite->city->name,
                ])
            </div>
        </div>
    </section>
</x-layouts.microsite>
