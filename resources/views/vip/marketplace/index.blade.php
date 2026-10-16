<x-layouts.app title="Sell Products" heading="Sell Products on My Store">
    @php
        $money = fn ($n) => '₹'.number_format((float) $n, 0);
        // Everything the calculator needs, for products that have both a price and
        // a commission rule — there is nothing to compute without both.
        $calcProducts = $products
            ->filter(fn ($p) => ($earnings[$p->id]['price'] ?? 0) > 0 && ($earnings[$p->id]['amount'] ?? 0) > 0)
            ->map(fn ($p) => [
                'name' => $p->name,
                'price' => round($earnings[$p->id]['price'], 2),
                'earn' => round($earnings[$p->id]['amount'], 2),
                'percent' => $earnings[$p->id]['percent'],
            ])
            ->values();
        $percentLabel = fn ($percent) => rtrim(rtrim(number_format($percent, 2), '0'), '.').'%';
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-5">
        <p class="fs-sm text-secondary mw-2xl">
            Every company product is already on sale on your page. Turn one off if you would rather
            not list it. You control visibility, featured and order — pricing, details and commission
            are managed by the company.
        </p>
        @if ($microsite)
            <a href="{{ url($microsite->publicPath()) }}#shop" target="_blank"
               class="btn btn-brand btn-sm text-nowrap">View my store ↗</a>
        @endif
    </div>

    {{-- Profit calculator: what a sale off their own page actually pays them. Rates
         come from the same rules that settle a real order, so nothing here is a
         guess or a marketing number. --}}
    @if ($calcProducts->isNotEmpty())
        <div class="rounded-4 p-5 p-sm-6 mb-6 text-white premium-shadow" style="background: linear-gradient(to bottom right, var(--color-brand-900), var(--color-brand-950))"
             data-profit-calc data-products="{{ json_encode($calcProducts) }}">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="d-flex h-7 w-7 align-items-center justify-content-center rounded-2 bg-white bg-opacity-10 text-gold-400">
                    <x-icon name="rupee" class="w-4 h-4" />
                </span>
                <h2 class="fw-semibold">Profit Calculator</h2>
            </div>
            <p class="fs-sm text-brand- mb-5">
                What you earn when you sell from your own web page.
            </p>

            <div class="d-grid grid-cols-md-3 gap-4 mb-5">
                <div>
                    <label for="calc-product" class="d-block fs-xs text-uppercase tracking-wide text-brand- mb-2">Product</label>
                    <select id="calc-product" data-calc-product
                            class="w-100 rounded-2 bg-white bg-opacity-10 border-white border-opacity-25 text-white fs-sm px-3 py-2">
                        @foreach ($calcProducts as $index => $item)
                            <option value="{{ $index }}" class="text-body">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="calc-units" class="d-block fs-xs text-uppercase tracking-wide text-brand- mb-2">Units sold</label>
                    <input type="number" id="calc-units" data-calc-units value="10" min="1" step="1"
                           class="w-100 rounded-2 bg-white bg-opacity-10 border-white border-opacity-25 text-white fs-sm px-3 py-2">
                </div>
                <div>
                    <p class="d-block fs-xs text-uppercase tracking-wide text-brand- mb-2">Sale price each</p>
                    <p class="fs-lg fw-semibold py-2" data-calc-price>—</p>
                </div>
            </div>

            <div class="d-grid grid-cols-sm-3 gap-4">
                <div class="bg-white bg-opacity-10 rounded-3 px-4 py-3 border border-white border-opacity-10">
                    <p class="fs-xs text-uppercase tracking-wide text-brand- mb-1">Your commission</p>
                    <p class="fs-xl fw-bold text-gold-400" data-calc-rate>—</p>
                </div>
                <div class="bg-white bg-opacity-10 rounded-3 px-4 py-3 border border-white border-opacity-10">
                    <p class="fs-xs text-uppercase tracking-wide text-brand- mb-1">You earn per sale</p>
                    <p class="fs-xl fw-bold text-gold-400" data-calc-each>—</p>
                </div>
                <div class="rounded-3 px-4 py-3 border" style="background-color: color-mix(in srgb, var(--color-gold-400) 10%, transparent); border-color: color-mix(in srgb, var(--color-gold-400) 30%, transparent) !important">
                    <p class="fs-xs text-uppercase tracking-wide text-gold-400 mb-1">Total you earn</p>
                    <p class="fs-2xl fw-bolder text-gold-400" data-calc-total>—</p>
                </div>
            </div>

            <p class="fs-xs text-brand- mt-4">
                Commission is credited to your wallet once the order is delivered, not at checkout.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('vip.marketplace.update') }}">
        @csrf
        @method('PUT')
        <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-start text-secondary">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">You earn</th>
                        <th class="px-4 py-3 text-center">Show</th>
                        <th class="px-4 py-3 text-center">Featured</th>
                        <th class="px-4 py-3 w-24">Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $pivot = $pivots[$product->id] ?? null;
                            // No saved row means the member has never touched this
                            // product, and the catalog sells by default.
                            $shown = $pivot === null || (bool) $pivot->is_visible;
                            $earning = $earnings[$product->id] ?? null;
                        @endphp
                        <tr class="border-top">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-11 h-11 rounded-2 bg-body-tertiary d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0">
                                        @if ($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="w-100 h-100 object-fit-contain">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="fw-medium text-body text-truncate">{{ $product->name }}</p>
                                        <p class="fs-xs text-secondary">{{ $product->category?->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-nowrap">
                                @if ($product->hasPrice())
                                    <span class="fw-medium">{{ $money($product->sellingPrice()) }}</span>
                                    @if ($product->hasDiscount())
                                        <span class="fs-xs text-secondary text-decoration-line-through ms-1">{{ $money($product->mrp) }}</span>
                                    @endif
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-nowrap">
                                @if ($earning && $earning['amount'] > 0)
                                    <span class="fw-semibold text-success">{{ $money($earning['amount']) }}</span>
                                    @if ($earning['percent'] !== null)
                                        <span class="fs-xs text-secondary ms-1">({{ $percentLabel($earning['percent']) }})</span>
                                    @endif
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="products[{{ $product->id }}][show]" value="1"
                                       class="rounded text-brand" @checked($shown)>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="products[{{ $product->id }}][featured]" value="1"
                                       class="rounded text-gold" @checked($pivot && $pivot->is_featured)>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" min="0" name="products[{{ $product->id }}][order]" value="{{ $pivot->display_order ?? 0 }}"
                                       class="w-20 rounded-2 shadow-sm fs-sm">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-secondary">No products available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
          </div>
        </div>

        @if ($products->isNotEmpty())
            <div class="mt-4">
                <button type="submit" class="btn btn-brand btn-sm">Save My Store</button>
            </div>
        @endif
    </form>
</x-layouts.app>
