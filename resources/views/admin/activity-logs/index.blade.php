<x-layouts.app title="Activity Log" heading="Activity Log">
    <form method="GET" class="mb-4 d-flex align-items-center gap-3">
        <label for="causer_id" class="fs-sm text-body-secondary">Filter by user</label>
        <select id="causer_id" name="causer_id" onchange="this.form.submit()"
                class="rounded-2 fs-sm">
            <option value="">All Branch Managers &amp; Commission Partners</option>
            @foreach ($causers as $causer)
                <option value="{{ $causer->id }}" @selected($selectedCauserId === $causer->id)>
                    {{ $causer->name }} ({{ $causer->getRoleNames()->first() }})
                </option>
            @endforeach
        </select>
    </form>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
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
                    <tr class="border-top align-top">
                        <td class="px-4 py-3 text-nowrap text-secondary">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ $activity->causer?->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-capitalize">{{ $activity->description }}</td>
                        <td class="px-4 py-3">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                        <td class="px-4 py-3 fs-xs text-secondary">
                            @if ($activity->properties->isNotEmpty())
                                <pre class="text-prewrap font-monospace">{{ $activity->properties->toJson(JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>
</x-layouts.app>
