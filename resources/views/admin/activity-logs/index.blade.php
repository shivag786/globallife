<x-layouts.app title="Activity Log" heading="Activity Log">
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label for="causer_id" class="text-sm text-slate-600">Filter by user</label>
        <select id="causer_id" name="causer_id" onchange="this.form.submit()"
                class="rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Branch Managers &amp; Commission Partners</option>
            @foreach ($causers as $causer)
                <option value="{{ $causer->id }}" @selected($selectedCauserId === $causer->id)>
                    {{ $causer->name }} ({{ $causer->getRoleNames()->first() }})
                </option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr class="border-t border-slate-100 align-top">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ $activity->causer?->name ?? 'System' }}</td>
                        <td class="px-4 py-3 capitalize">{{ $activity->description }}</td>
                        <td class="px-4 py-3">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            @if ($activity->properties->isNotEmpty())
                                <pre class="whitespace-pre-wrap font-mono text-[11px]">{{ $activity->properties->toJson(JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>
</x-layouts.app>
