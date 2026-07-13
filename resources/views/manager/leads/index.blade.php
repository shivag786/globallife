<x-layouts.app title="Leads" heading="My Leads">
    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
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
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $lead->name }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $lead->email }}</div>
                            @if ($lead->phone)<div class="text-xs text-slate-400">{{ $lead->phone }}</div>@endif
                        </td>
                        <td class="px-4 py-3">{{ $lead->city ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $lead->interestedPlan?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs bg-indigo-50 text-indigo-700 capitalize">{{ $lead->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No leads assigned to you yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
</x-layouts.app>
