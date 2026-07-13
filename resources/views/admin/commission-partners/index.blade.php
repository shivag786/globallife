<x-layouts.app title="Commission Partners" heading="Commission Partners">
    <p class="text-sm text-slate-500 mb-4">
        Read-only oversight. Commission Partners are registered and managed by their Branch Manager.
    </p>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
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
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $partner->name }}</td>
                        <td class="px-4 py-3">{{ $partner->email }}</td>
                        <td class="px-4 py-3">{{ $partner->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $partner->cities->pluck('name')->implode(', ') }}</td>
                        <td class="px-4 py-3">{{ $partner->commission_percentage !== null ? $partner->commission_percentage.'%' : '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs {{ $partner->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                {{ $partner->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">No commission partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
