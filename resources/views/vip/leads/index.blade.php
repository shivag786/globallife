<x-layouts.app title="Leads" heading="Leads">
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('vip.leads.export') }}" class="bg-body border text-body-secondary fs-sm px-4 py-2 rounded-2">
            Export CSV
        </a>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">City</th>
                    <th class="px-4 py-3">Message</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leads as $lead)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $lead->name }}</td>
                        <td class="px-4 py-3">{{ $lead->email }}<br><span class="text-secondary">{{ $lead->phone }}</span></td>
                        <td class="px-4 py-3">{{ $lead->city }}</td>
                        <td class="px-4 py-3 mw-xs text-truncate">{{ $lead->message }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs bg-body-secondary text-body-secondary">{{ ucfirst($lead->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-secondary">{{ $lead->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-secondary">No leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
</x-layouts.app>
