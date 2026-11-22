<x-layouts.app title="Announcements" heading="Announcements">
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Post to the employee notice board</div>
        <form method="POST" action="{{ route('admin.hr.announcements.store') }}" class="card-body d-flex flex-column gap-2">
            @csrf
            <input name="title" class="form-control" placeholder="Title" required>
            <textarea name="body" rows="3" class="form-control" placeholder="Message" required></textarea>
            <button class="btn btn-brand btn-sm align-self-start">Publish</button>
        </form>
    </div>

    @forelse ($announcements as $a)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-2">
                    <div>
                        <h3 class="h6 fw-semibold mb-1">{{ $a->title }}
                            @unless ($a->is_active) <span class="badge text-bg-secondary">Hidden</span> @endunless
                        </h3>
                        <div class="fs-xs text-secondary mb-2">{{ $a->created_at->format('d M Y, h:i A') }} &middot; {{ $a->publisher?->name }}</div>
                    </div>
                    <div class="text-nowrap">
                        <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#ann-{{ $a->id }}">Edit</button>
                        <form method="POST" action="{{ route('admin.hr.announcements.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Delete this announcement?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0 ms-2">Delete</button>
                        </form>
                    </div>
                </div>
                <p class="mb-0 small" style="white-space: pre-line">{{ $a->body }}</p>
                <form method="POST" action="{{ route('admin.hr.announcements.update', $a) }}" class="collapse mt-3 d-flex flex-column gap-2" id="ann-{{ $a->id }}">
                    @csrf @method('PUT')
                    <input name="title" value="{{ $a->title }}" class="form-control form-control-sm" required>
                    <textarea name="body" rows="3" class="form-control form-control-sm" required>{{ $a->body }}</textarea>
                    <label class="form-check small"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($a->is_active)> Visible to employees</label>
                    <button class="btn btn-sm btn-outline-secondary align-self-start">Save</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-secondary">No announcements yet.</p>
    @endforelse

    {{ $announcements->links() }}
</x-layouts.app>
