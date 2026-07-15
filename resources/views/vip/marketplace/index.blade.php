<x-layouts.app title="Sell Products" heading="Sell Products on My Store">
    @php $money = fn ($n) => '₹'.number_format((float) $n, 0); @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <p class="text-sm text-slate-500 max-w-2xl">
            Turn products on to show them on your public storefront. You set visibility, featured
            and order — pricing, details and commission are managed by the company.
        </p>
        @if ($microsite)
            <a href="{{ url($microsite->publicPath()) }}#shop" target="_blank"
               class="text-sm bg-brand-600 text-white px-3 py-1.5 rounded-md hover:bg-brand-700 whitespace-nowrap">View my store ↗</a>
        @endif
    </div>

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
                        <th class="px-4 py-3 text-center">Show</th>
                        <th class="px-4 py-3 text-center">Featured</th>
                        <th class="px-4 py-3 w-24">Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php $pivot = $pivots[$product->id] ?? null; @endphp
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
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" name="products[{{ $product->id }}][show]" value="1"
                                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" @checked($pivot)>
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
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No products available yet.</td></tr>
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
