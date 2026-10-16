<x-layouts.app :title="'Commission — '.$product->name" heading="Product Commission">
    @php
        $sourceLabels = ['product' => 'This product', 'category' => 'Category', 'global' => 'Global', 'none' => 'Not set'];
        $sourceClasses = ['product' => 'bg-brand-subtle text-brand', 'category' => 'bg-primary-subtle text-primary', 'global' => 'bg-body-secondary text-secondary', 'none' => 'bg-body-secondary text-secondary'];
        $money = fn ($n) => '₹'.number_format((float) $n, 2);
    @endphp

    <div class="mb-4">
        <a href="{{ route('admin.commissions.index') }}" class="fs-sm text-primary hover-underline">← Back to commissions</a>
    </div>
    <p class="fs-sm text-secondary mb-6 mw-2xl">
        Commission specific to <strong class="text-body-secondary">{{ $product->name }}</strong>.
        Any role left blank falls back to the category, then the global default.
    </p>

    <div class="d-grid grid-cols-lg-2 gap-6 align-items-start mw-4xl">
        {{-- Rules form --}}
        <form method="POST" action="{{ route('admin.commissions.product.update', $product) }}" class="bg-body rounded-2 shadow-sm border p-6">
            @csrf
            @method('PUT')
            @include('admin.commissions._rules_fields')
            <button type="submit" class="btn btn-primary btn-sm mt-6">Save Product Commission</button>
        </form>

        {{-- Live split preview (server-computed from the saved rules) --}}
        <div class="bg-dark text-white rounded-2 shadow-sm p-6">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="fw-semibold">Split preview</h2>
                <span class="fs-xs text-secondary">on a {{ $money($preview['base']) }} sale</span>
            </div>
            @if ($preview['base'] <= 0)
                <p class="fs-sm text-warning mt-4">Set a selling or offer price on this product to preview the split.</p>
            @endif
            <div class="mt-5 d-flex flex-column gap-3">
                @foreach ($preview['lines'] as $line)
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-sm">{{ $line['label'] }}</span>
                            <span class="px-2 py-1 rounded {{ $sourceClasses[$line['source']] }}">{{ $sourceLabels[$line['source']] }}</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-semibold font-monospace">{{ $money($line['amount']) }}</span>
                            @if ($line['type'])
                                <span class="fs-xs text-secondary ms-1">({{ $line['type'] === 'percent' ? rtrim(rtrim($line['value'], '0'), '.').'%' : 'fixed' }})</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="d-flex align-items-center justify-content-between border-top border-white border-opacity-10 pt-3 mt-3">
                    <span class="fs-sm text-body-tertiary">Company (remainder)</span>
                    <span class="fw-semibold font-monospace text-brand">{{ $money($preview['company']) }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between border-top border-white border-opacity-10 pt-3">
                    <span class="fs-sm fw-medium">Total</span>
                    <span class="fw-bold font-monospace">{{ $money($preview['base']) }}</span>
                </div>
            </div>
            <p class="fs-xs text-secondary mt-4">Preview reflects saved rules and assumes each role exists in the seller's chain. At sale time, any missing role folds into the company share.</p>
        </div>
    </div>
</x-layouts.app>
