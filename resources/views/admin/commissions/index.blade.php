<x-layouts.app title="Product Commissions" heading="Product Commissions">
    <p class="text-sm text-slate-500 mb-6 max-w-3xl">
        Configure how product-sale commission is split. Rules resolve most-specific first:
        a <strong>product</strong> rule overrides its <strong>category</strong> rule, which overrides the
        <strong>global</strong> default. The company keeps whatever remains. This is separate from VIP joining commission.
    </p>

    {{-- Global defaults --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 p-5 mb-6 max-w-3xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Global Default Commission</h2>
            <a href="{{ route('admin.commissions.global.edit') }}" class="text-sm bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700">Configure</a>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($roles as $role => $label)
                @php $rule = $global[$role] ?? null; @endphp
                <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-full {{ $rule ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-400' }}">
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

    <div class="grid lg:grid-cols-2 gap-6 max-w-5xl">
        {{-- By category --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">By Category</div>
            <ul class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span class="text-sm text-slate-700">{{ $category->name }}</span>
                        <div class="flex items-center gap-3">
                            @if ($categoryScoped->contains($category->id))
                                <span class="text-xs bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full">Custom</span>
                            @else
                                <span class="text-xs text-slate-400">Inherits global</span>
                            @endif
                            <a href="{{ route('admin.commissions.category.edit', $category) }}" class="text-sm text-indigo-600 hover:underline">Set</a>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-400 text-sm">No categories yet.</li>
                @endforelse
            </ul>
        </div>

        {{-- By product --}}
        <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">By Product</div>
            <ul class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm text-slate-700 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400">{{ $product->category?->name ?? 'Uncategorised' }} · {{ $product->sellingPrice() ? '₹'.number_format($product->sellingPrice(), 2) : 'no price' }}</p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            @if ($productScoped->contains($product->id))
                                <span class="text-xs bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full">Custom</span>
                            @endif
                            <a href="{{ route('admin.commissions.product.edit', $product) }}" class="text-sm text-indigo-600 hover:underline">Set</a>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-400 text-sm">No products yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-layouts.app>
