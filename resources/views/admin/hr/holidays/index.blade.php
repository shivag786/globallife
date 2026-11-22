<x-layouts.app title="Holidays" heading="Holiday Calendar {{ $year }}">
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-4">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.hr.holidays.index', ['year' => $year - 1]) }}" class="btn btn-outline-secondary">&larr; {{ $year - 1 }}</a>
            <a href="{{ route('admin.hr.holidays.index', ['year' => $year + 1]) }}" class="btn btn-outline-secondary">{{ $year + 1 }} &rarr;</a>
        </div>
        <form method="POST" action="{{ route('admin.hr.holidays.store') }}" class="d-flex gap-2">
            @csrf
            <input type="date" name="date" class="form-control form-control-sm" required>
            <input name="name" class="form-control form-control-sm" placeholder="e.g. Diwali" required>
            <button class="btn btn-brand btn-sm text-nowrap">Add holiday</button>
        </form>
    </div>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <table class="w-100 fs-sm">
            <thead class="bg-body-tertiary text-secondary">
                <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Day</th><th class="px-4 py-3">Holiday</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody>
                @forelse ($holidays as $h)
                    <tr class="border-top">
                        <td class="px-4 py-3">{{ $h->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $h->date->format('l') }}</td>
                        <td class="px-4 py-3 fw-medium">{{ $h->name }}</td>
                        <td class="px-4 py-3 text-end text-nowrap">
                            <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#hol-{{ $h->id }}">Edit</button>
                            <form method="POST" action="{{ route('admin.hr.holidays.destroy', $h) }}" class="d-inline" onsubmit="return confirm('Remove this holiday?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0 ms-2">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="hol-{{ $h->id }}">
                        <td colspan="4" class="px-4 pb-3">
                            <form method="POST" action="{{ route('admin.hr.holidays.update', $h) }}" class="d-flex gap-2">
                                @csrf @method('PUT')
                                <input type="date" name="date" value="{{ $h->date->format('Y-m-d') }}" class="form-control form-control-sm" required>
                                <input name="name" value="{{ $h->name }}" class="form-control form-control-sm" required>
                                <button class="btn btn-sm btn-outline-secondary">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-secondary">No holidays added for {{ $year }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
