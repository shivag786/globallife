<x-layouts.app title="Homepage Builder" heading="Homepage Builder">
    <p class="fs-sm text-secondary mb-4">
        Sections render on the public homepage top-to-bottom in the order below. Toggle a section off to hide it without deleting it.
    </p>

    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.home-sections.create') }}" class="btn btn-primary btn-sm">
            + Add Section
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    <tr class="border-top">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center gap-1">
                                {{ $section->display_order }}
                                <form action="{{ route('admin.home-sections.move-up', $section) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-secondary" title="Move up">&uarr;</button>
                                </form>
                                <form action="{{ route('admin.home-sections.move-down', $section) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-secondary" title="Move down">&darr;</button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs bg-primary-subtle text-primary text-capitalize">{{ $section->type }}</span>
                        </td>
                        <td class="px-4 py-3 fw-medium">{{ $section->title }}</td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.home-sections.toggle-status', $section) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-2 py-1 rounded fs-xs {{ $section->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                    {{ $section->status }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('admin.home-sections.edit', $section) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('admin.home-sections.destroy', $section) }}" method="POST" class="d-inline" data-confirm="Delete this section?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No homepage sections yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
