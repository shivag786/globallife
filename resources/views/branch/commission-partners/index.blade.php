<x-layouts.app title="Commission Partners" heading="Commission Partners">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('branch.commission-partners.create') }}" class="btn btn-brand btn-sm">
            + Add Commission Partner
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Cities</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partners as $partner)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $partner->name }}</td>
                        <td class="px-4 py-3">{{ $partner->email }}</td>
                        <td class="px-4 py-3 text-secondary">
                            {{ $partner->cities->isEmpty() ? '—' : $partner->cities->pluck('name')->implode(', ') }}
                        </td>
                        <td class="px-4 py-3">{{ $partner->commission_percentage }}%</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $partner->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                {{ $partner->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('branch.commission-partners.edit', $partner) }}" class="text-primary hover-underline">Edit</a>
                            <form action="{{ route('branch.commission-partners.toggle-status', $partner) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none text-warning hover-underline">
                                    {{ $partner->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">You haven't added any Commission Partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
