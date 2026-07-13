<x-layouts.app title="Leads" heading="Leads">
    <div class="flex justify-end mb-4">
        <a href="{{ route('vip.leads.export') }}" class="bg-white border border-slate-200 text-slate-700 text-sm px-4 py-2 rounded-md hover:bg-slate-50">
            Export CSV
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
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
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $lead->name }}</td>
                        <td class="px-4 py-3">{{ $lead->email }}<br><span class="text-slate-400">{{ $lead->phone }}</span></td>
                        <td class="px-4 py-3">{{ $lead->city }}</td>
                        <td class="px-4 py-3 max-w-xs truncate">{{ $lead->message }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">{{ ucfirst($lead->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $lead->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $leads->links() }}</div>
</x-layouts.app>
