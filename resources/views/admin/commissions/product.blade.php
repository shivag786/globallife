<x-layouts.app :title="'Commission — '.$product->name" heading="Product Commission">
    @php
        $sourceLabels = ['product' => 'This product', 'category' => 'Category', 'global' => 'Global', 'none' => 'Not set'];
        $sourceClasses = ['product' => 'bg-brand-50 text-brand-700', 'category' => 'bg-indigo-50 text-indigo-700', 'global' => 'bg-slate-100 text-slate-500', 'none' => 'bg-slate-100 text-slate-400'];
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
    @endphp

    <div class="mb-4">
        <a href="{{ route('admin.commissions.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to commissions</a>
    </div>
    <p class="text-sm text-slate-500 mb-6 max-w-2xl">
        Commission specific to <strong class="text-slate-700">{{ $product->name }}</strong>.
        Any role left blank falls back to the category, then the global default.
    </p>

    <div class="grid lg:grid-cols-2 gap-6 items-start max-w-4xl">
        {{-- Rules form --}}
        <form method="POST" action="{{ route('admin.commissions.product.update', $product) }}" class="bg-white rounded-lg shadow-sm border border-slate-100 p-6">
            @csrf
            @method('PUT')
            @include('admin.commissions._rules_fields')
            <button type="submit" class="mt-6 bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">Save Product Commission</button>
        </form>

        {{-- Live split preview (server-computed from the saved rules) --}}
        <div class="bg-slate-900 text-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Split preview</h2>
                <span class="text-xs text-slate-400">on a {{ $money($preview['base']) }} sale</span>
            </div>
            @if ($preview['base'] <= 0)
                <p class="text-sm text-amber-300 mt-4">Set a selling or offer price on this product to preview the split.</p>
            @endif
            <div class="mt-5 space-y-3">
                @foreach ($preview['lines'] as $line)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">{{ $line['label'] }}</span>
                            <span class="text-[0.65rem] px-1.5 py-0.5 rounded {{ $sourceClasses[$line['source']] }}">{{ $sourceLabels[$line['source']] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold tabular-nums">{{ $money($line['amount']) }}</span>
                            @if ($line['type'])
                                <span class="text-xs text-slate-400 ml-1">({{ $line['type'] === 'percent' ? rtrim(rtrim($line['value'], '0'), '.').'%' : 'fixed' }})</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="flex items-center justify-between border-t border-white/10 pt-3 mt-3">
                    <span class="text-sm text-slate-300">Company (remainder)</span>
                    <span class="font-semibold tabular-nums text-brand-300">{{ $money($preview['company']) }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-white/10 pt-3">
                    <span class="text-sm font-medium">Total</span>
                    <span class="font-bold tabular-nums">{{ $money($preview['base']) }}</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">Preview reflects saved rules and assumes each role exists in the seller's chain. At sale time, any missing role folds into the company share.</p>
        </div>
    </div>
</x-layouts.app>
