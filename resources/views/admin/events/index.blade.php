<x-layouts.app title="Events" heading="Events">
    <div class="d-flex justify-content-end mb-4">
        @can('events.create')
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">
                + Add Event
            </a>
        @endcan
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-start text-secondary">
                <tr>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Location</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr class="border-top">
                        <td class="px-4 py-3 fw-medium">{{ $event->title }}</td>
                        <td class="px-4 py-3">{{ $event->event_date->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3">{{ $event->location ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded fs-xs {{ $event->status === 'active' ? 'bg-success-subtle text-success' : 'bg-body-secondary text-secondary' }}">
                                {{ $event->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end d-flex align-items-center gap-3">
                            <a href="{{ route('events.show', $event) }}" class="text-secondary hover-underline" target="_blank">View</a>
                            @can('events.edit')
                                <a href="{{ route('admin.events.edit', $event) }}" class="text-primary hover-underline">Edit</a>
                            @endcan
                            @can('events.delete')
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" data-confirm="Delete this event?" data-confirm-danger>
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link p-0 text-decoration-none text-danger hover-underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
      </div>
    </div>
</x-layouts.app>
