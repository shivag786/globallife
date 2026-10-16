<x-layouts.app title="Commission Partners" heading="Commission Partners">
    <p class="fs-sm text-secondary mb-4">
        Read-only oversight. Commission Partners are registered and managed by their Branch Manager.
    </p>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Branch Manager</th>
                    <th class="px-4 py-3">Cities</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partners as $partner)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $partner->name }}</td>
                        <td class="px-4 py-3">{{ $partner->email }}</td>
                        <td class="px-4 py-3">{{ $partner->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">
                            {{ $partner->cities->isEmpty() ? '—' : $partner->cities->pluck('name')->implode(', ') }}
                        </td>
                        <td class="px-4 py-3">{{ $partner->commission_percentage !== null ? $partner->commission_percentage.'%' : '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $partner->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                {{ $partner->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No commission partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
