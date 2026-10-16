<x-layouts.app title="Products" heading="Products">
    <div class="d-flex justify-content-end mb-4">
        @can('products.create')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                + Add Product
            </a>
        @endcan
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Featured</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->category }}</td>
                        <td class="px-4 py-3">{{ $product->is_featured ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $product->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('products.show', $product) }}" class="text-secondary hover-underline" target="_blank">View</a>
                            @can('products.edit')
                                <a href="{{ route('admin.products.benefits.index', $product) }}" class="text-brand hover-underline">Benefits</a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-primary hover-underline">Edit</a>
                            @endcan
                            @can('products.delete')
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" data-confirm="Delete this product?" data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
