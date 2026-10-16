<x-layouts.app title="Product Commissions" heading="Product Commissions">
    <p class="fs-sm text-secondary mb-6 mw-3xl">
        Configure how product-sale commission is split. Rules resolve most-specific first:
        a <strong>product</strong> rule overrides its <strong>category</strong> rule, which overrides the
        <strong>global</strong> default. The company keeps whatever remains. This is separate from VIP joining commission.
    </p>

    {{-- Global defaults --}}
    <div class="bg-body rounded-2 shadow-sm border p-5 mb-6 mw-3xl">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="fw-semibold text-body">Global Default Commission</h2>
            <a href="{{ route('admin.commissions.global.edit') }}" class="btn btn-primary btn-sm">Configure</a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($roles as $role => $label)
                @php $rule = $global[$role] ?? null; @endphp
                <span class="d-inline-flex align-items-center gap-2 fs-sm px-3 py-2 rounded-pill {{ $rule ? 'bg-brand-subtle text-brand' : 'bg-body-secondary text-secondary' }}">
                    {{ $label }}:
                    <strong>
                        @if ($rule)
                            {{ $rule->type === 'percent' ? rtrim(rtrim($rule->value, '0'), '.').'%' : '₹'.number_format($rule->value, 2) }}
                        @else
                            not set
                        @endif
                    </strong>
                </span>
            @endforeach
        </div>
    </div>

    <div class="d-grid grid-cols-lg-2 gap-6 mw-5xl">
        {{-- By category --}}
        <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
            <div class="px-5 py-3 border-bottom fw-semibold text-body">By Category</div>
            <ul class="divide-y">
                @forelse ($categories as $category)
                    <li class="d-flex align-items-center justify-content-between px-5 py-3">
                        <span class="fs-sm text-body-secondary">{{ $category->name }}</span>
                        <div class="d-flex align-items-center gap-3">
                            @if ($categoryScoped->contains($category->id))
                                <span class="fs-xs bg-brand-subtle text-brand px-2 py-1 rounded-pill">Custom</span>
                            @else
                                <span class="fs-xs text-secondary">Inherits global</span>
                            @endif
                            <a href="{{ route('admin.commissions.category.edit', $category) }}" class="fs-sm text-primary hover-underline">Set</a>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-secondary fs-sm">No categories yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- By product --}}
        <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
            <div class="px-5 py-3 border-bottom fw-semibold text-body">By Product</div>
            <ul class="divide-y">
                @forelse ($products as $product)
                    <li class="d-flex align-items-center justify-content-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="fs-sm text-body-secondary text-truncate">{{ $product->name }}</p>
                            <p class="fs-xs text-secondary">{{ $product->category?->name ?? 'Uncategorised' }} · {{ $product->sellingPrice() ? '₹'.number_format($product->sellingPrice(), 2) : 'no price' }}</p>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-shrink-0">
                            @if ($productScoped->contains($product->id))
                                <span class="fs-xs bg-brand-subtle text-brand px-2 py-1 rounded-pill">Custom</span>
                            @endif
                            <a href="{{ route('admin.commissions.product.edit', $product) }}" class="fs-sm text-primary hover-underline">Set</a>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-secondary fs-sm">No products yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts.app>
