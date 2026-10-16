<x-layouts.app title="Cities" heading="Cities">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.cities.create') }}" class="btn btn-primary btn-sm">
            + Add City
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">State</th>
                    <th class="px-4 py-3">Managers</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cities as $city)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $city->name }}</td>
                        <td class="px-4 py-3">{{ $city->state }}</td>
                        <td class="px-4 py-3">{{ $city->managers_count }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $city->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ $city->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('admin.cities.edit', $city) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('admin.cities.destroy', $city) }}" method="POST" class="d-inline" data-confirm="Delete this city?" data-confirm-danger>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No cities yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
