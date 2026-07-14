<x-layouts.app title="Products" heading="Products">
    <div class="flex justify-end mb-4">
        <a href="{{ route('vip.products.create') }}" class="bg-brand-700 text-white text-sm px-4 py-2 rounded-md hover:bg-brand-800">
            + Add Product
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Stock</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $product->name }} @if($product->is_featured) <span class="text-xs bg-gold-400/20 text-gold-600 px-2 py-0.5 rounded">Featured</span> @endif</td>
                        <td class="px-4 py-3">{{ $product->sku ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $product->stock ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $product->offer_price ?? $product->mrp ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $product->status === 'published' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('vip.products.edit', $product) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('vip.products.destroy', $product) }}" method="POST" class="inline" data-confirm="Delete this product?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
