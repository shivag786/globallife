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

    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <p class="text-sm text-slate-500 max-w-2xl">
            Every company product is already on sale on your page. Turn one off if you would rather
            not list it. You control visibility, featured and order — pricing, details and commission
            are managed by the company.
        </p>
        @if ($microsite)
            <a href="{{ url($microsite->publicPath()) }}#shop" target="_blank"
               class="text-sm bg-brand-600 text-white px-3 py-1.5 rounded-md hover:bg-brand-700 whitespace-nowrap">View my store ↗</a>
        @endif
    </div>

    {{-- Profit calculator: what a sale off their own page actually pays them. Rates
         come from the same rules that settle a real order, so nothing here is a
         guess or a marketing number. --}}
    @if ($calcProducts->isNotEmpty())
        <div class="bg-gradient-to-br from-brand-900 to-brand-950 rounded-2xl p-5 sm:p-6 mb-6 text-white premium-shadow"
             data-profit-calc data-products="{{ json_encode($calcProducts) }}">
            <div class="flex items-center gap-2 mb-1">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/10 text-gold-400">
                    <x-icon name="rupee" class="w-4 h-4" />
                </span>
                <h2 class="font-semibold">Profit Calculator</h2>
            </div>
            <p class="text-sm text-brand-200 mb-5">
                What you earn when you sell from your own web page.
            </p>

            <div class="grid md:grid-cols-3 gap-4 mb-5">
                <div>
                    <label for="calc-product" class="block text-xs uppercase tracking-wide text-brand-300 mb-1.5">Product</label>
                    <select id="calc-product" data-calc-product
                            class="w-full rounded-lg bg-white/10 border-white/20 text-white text-sm px-3 py-2.5 focus:border-gold-400 focus:ring-gold-400/30">
                        @foreach ($calcProducts as $index => $item)
                            <option value="{{ $index }}" class="text-slate-800">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="calc-units" class="block text-xs uppercase tracking-wide text-brand-300 mb-1.5">Units sold</label>
                    <input type="number" id="calc-units" data-calc-units value="10" min="1" step="1"
                           class="w-full rounded-lg bg-white/10 border-white/20 text-white text-sm px-3 py-2.5 focus:border-gold-400 focus:ring-gold-400/30">
                </div>
                <div>
                    <p class="block text-xs uppercase tracking-wide text-brand-300 mb-1.5">Sale price each</p>
                    <p class="text-lg font-semibold py-1.5" data-calc-price>—</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <div class="bg-white/5 rounded-xl px-4 py-3 border border-white/10">
                    <p class="text-xs uppercase tracking-wide text-brand-300 mb-1">Your commission</p>
                    <p class="text-xl font-bold text-gold-400" data-calc-rate>—</p>
                </div>
                <div class="bg-white/5 rounded-xl px-4 py-3 border border-white/10">
                    <p class="text-xs uppercase tracking-wide text-brand-300 mb-1">You earn per sale</p>
                    <p class="text-xl font-bold text-gold-400" data-calc-each>—</p>
                </div>
                <div class="bg-gold-400/10 rounded-xl px-4 py-3 border border-gold-400/30">
                    <p class="text-xs uppercase tracking-wide text-gold-200 mb-1">Total you earn</p>
                    <p class="text-2xl font-extrabold text-gold-400" data-calc-total>—</p>
                </div>
            </div>

            <p class="text-xs text-brand-300 mt-4">
                Commission is credited to your wallet once the order is delivered, not at checkout.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('vip.marketplace.update') }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
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
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-lg bg-slate-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @if ($product->main_image)
                                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="" class="w-full h-full object-contain">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-slate-800 truncate">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $product->category?->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($product->hasPrice())
                                    <span class="font-medium">{{ $money($product->sellingPrice()) }}</span>
                                    @if ($product->hasDiscount())
                                        <span class="text-xs text-slate-400 line-through ml-1">{{ $money($product->mrp) }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($earning && $earning['amount'] > 0)
                                    <span class="font-semibold text-emerald-600">{{ $money($earning['amount']) }}</span>
                                    @if ($earning['percent'] !== null)
                                        <span class="text-xs text-slate-400 ml-1">({{ $percentLabel($earning['percent']) }})</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="products[{{ $product->id }}][show]" value="1"
                                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked($shown)>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="products[{{ $product->id }}][featured]" value="1"
                                       class="rounded border-slate-300 text-gold-500 focus:ring-gold-400" @checked($pivot && $pivot->is_featured)>
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" min="0" name="products[{{ $product->id }}][order]" value="{{ $pivot->display_order ?? 0 }}"
                                       class="w-20 rounded-md border-slate-300 shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No products available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
          </div>
        </div>

        @if ($products->isNotEmpty())
            <div class="mt-4">
                <button type="submit" class="bg-brand-700 text-white text-sm px-5 py-2.5 rounded-md hover:bg-brand-800 font-medium">Save My Store</button>
            </div>
        @endif
    </form>
</x-layouts.app>
