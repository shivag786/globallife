<x-layouts.app title="Products" heading="Products">
    {{-- Plan quota. The cap is on the total, so items added under a bigger
         package still count after a downgrade. --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="fs-sm">
            <span class="text-secondary">Plan usage:</span>
            <span class="fw-semibold {{ $quota['can_add'] ? 'text-body' : 'text-danger' }}">
                {{ $quota['used'] }} / {{ $quota['limit'] }} products
            </span>
            @if ($quota['over'])
                <span class="ms-2 fs-xs text-danger">over your plan limit &mdash; nothing removed, but you cannot add more</span>
            @elseif (! $quota['can_add'])
                <span class="ms-2 fs-xs text-danger">limit reached</span>
            @else
                <span class="ms-2 fs-xs text-secondary">{{ $quota['remaining'] }} left</span>
            @endif
        </div>

        @if ($quota['can_add'])
            <a href="{{ route('vip.products.create') }}" class="btn btn-brand btn-sm">
                + Add Product
            </a>
        @else
            <span class="bg-body-secondary text-secondary fs-sm px-4 py-2 rounded-2"
                  title="Ask your Commission Partner to renew you onto a larger package.">
                + Add Product
            </span>
        @endif
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
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
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $product->name }} @if($product->is_featured) <span class="fs-xs bg-gold-subtle text-gold px-2 py-1 rounded">Featured</span> @endif</td>
                        <td class="px-4 py-3">{{ $product->sku ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $product->stock ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $product->offer_price ?? $product->mrp ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $product->status === 'published' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-body-secondary' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('vip.products.edit', $product) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('vip.products.destroy', $product) }}" method="POST" class="d-inline" data-confirm="Delete this product?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
