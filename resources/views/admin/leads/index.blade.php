<x-layouts.app title="Leads" heading="Leads">
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3">Manager</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leads as $lead)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $lead->name }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $lead->email }}</div>
                            @if ($lead->phone)<div class="fs-xs text-secondary">{{ $lead->phone }}</div>@endif
                        </td>
                        <td class="px-4 py-3">{{ $lead->city ?? '—' }}</td>
                        <td class="px-4 py-3 text-capitalize">{{ str_replace('_', ' ', $lead->source) }}</td>
                        <td class="px-4 py-3">{{ $lead->assignedManager?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs bg-primary-subtle text-primary text-capitalize">{{ $lead->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="text-primary hover-underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
</x-layouts.app>
