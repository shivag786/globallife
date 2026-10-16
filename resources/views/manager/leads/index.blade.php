<x-layouts.app title="Leads" heading="My Leads">
    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Interested Plan</th>
                    <th class="px-4 py-3">Status</th>
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
                        <td class="px-4 py-3">{{ $lead->interestedPlan?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs bg-primary-subtle text-primary text-capitalize">{{ $lead->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No leads assigned to you yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
</x-layouts.app>
