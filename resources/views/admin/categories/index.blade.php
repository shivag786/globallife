<x-layouts.app title="Categories" heading="Categories">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
            + Add Category
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Products</th>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-top">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if ($category->image)
                                    <img src="{{ asset('storage/'.$category->image) }}" alt="" class="w-9 h-9 rounded object-fit-cover">
                                @else
                                    <span class="w-9 h-9 rounded bg-body-secondary d-flex align-items-center justify-content-center text-secondary fs-xs">—</span>
                                @endif
                                <span class="fw-medium">{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">{{ $category->display_order }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $category->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ $category->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" data-confirm="Delete this category?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
