<x-layouts.app title="Assets" heading="Company Assets">
    @php
        $fields = function ($a = null) {
            $cats = collect(\App\Models\Hr\Asset::CATEGORIES)->map(fn ($c) => '<option '.($a?->category === $c ? 'selected' : '').'>'.e($c).'</option>')->implode('');
            return '
            <div class="col-md-3"><label class="form-label small">Name</label><input name="name" value="'.e($a?->name).'" class="form-control form-control-sm" placeholder="e.g. Dell Latitude 5420" required></div>
            <div class="col-md-2"><label class="form-label small">Asset tag</label><input name="asset_tag" value="'.e($a?->asset_tag).'" class="form-control form-control-sm" placeholder="GL-LAP-001" required></div>
            <div class="col-md-2"><label class="form-label small">Category</label><select name="category" class="form-select form-select-sm">'.$cats.'</select></div>
            <div class="col-md-2"><label class="form-label small">Serial no.</label><input name="serial_number" value="'.e($a?->serial_number).'" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Purchase date</label><input type="date" name="purchase_date" value="'.e($a?->purchase_date?->format('Y-m-d')).'" class="form-control form-control-sm"></div>
            <div class="col-md-1"><label class="form-label small">Cost</label><input type="number" step="0.01" min="0" name="purchase_cost" value="'.e($a?->purchase_cost).'" class="form-control form-control-sm"></div>
            <div class="col-12"><input name="notes" value="'.e($a?->notes).'" class="form-control form-control-sm" placeholder="Notes (optional)"></div>';
        };
    @endphp

    @if ($canCreate)
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-semibold">Add asset</div>
            <form method="POST" action="{{ route('admin.hr.assets.store') }}" class="card-body row g-2">
                @csrf
                {!! $fields() !!}
                <div class="col-12"><button class="btn btn-brand btn-sm">Add asset</button></div>
            </form>
        </div>
    @endif

    <form method="GET" class="d-flex flex-wrap gap-2 mb-3">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="width: 14rem" placeholder="Name, tag or serial">
        <select name="status" class="form-select form-select-sm" style="width: 10rem">
            <option value="">All statuses</option>
            @foreach (\App\Models\Hr\Asset::STATUSES as $k => $l) <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option> @endforeach
        </select>
        <select name="category" class="form-select form-select-sm" style="width: 10rem">
            <option value="">All categories</option>
            @foreach (\App\Models\Hr\Asset::CATEGORIES as $c) <option @selected(request('category') === $c)>{{ $c }}</option> @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr><th class="px-3 py-3">Asset</th><th class="px-3 py-3">Category</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Holder</th><th class="px-3 py-3"></th></tr>
                </thead>
                <tbody>
                    @forelse ($assets as $a)
                        <tr class="border-top align-top">
                            <td class="px-3 py-2">
                                <strong>{{ $a->name }}</strong>
                                <div class="fs-xs text-secondary font-monospace">{{ $a->asset_tag }} {{ $a->serial_number ? '· SN '.$a->serial_number : '' }}</div>
                            </td>
                            <td class="px-3 py-2">{{ $a->category }}</td>
                            <td class="px-3 py-2">@include('hr.partials.badge', ['status' => $a->status, 'label' => \App\Models\Hr\Asset::STATUSES[$a->status] ?? $a->status])</td>
                            <td class="px-3 py-2">
                                @if ($a->employee)
                                    {{ $a->employee->name }}<div class="fs-xs text-secondary">since {{ $a->assigned_on?->format('d M Y') }}</div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-end text-nowrap">
                                @if ($canEdit)
                                    @if ($a->status === 'available')
                                        <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#asg-{{ $a->id }}">Assign</button>
                                    @elseif ($a->status === 'assigned')
                                        <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#ret-{{ $a->id }}">Take back</button>
                                    @endif
                                    <button class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#edit-{{ $a->id }}">Edit</button>
                                @endif
                                <button class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#hist-{{ $a->id }}">History</button>
                                @if ($canDelete && $a->status !== 'assigned')
                                    <form method="POST" action="{{ route('admin.hr.assets.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Delete this asset?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0 ms-2">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @if ($canEdit)
                            <tr class="collapse" id="asg-{{ $a->id }}"><td colspan="5" class="px-3 pb-3">
                                <form method="POST" action="{{ route('admin.hr.assets.assign', $a) }}" class="d-flex flex-wrap gap-2">
                                    @csrf
                                    <select name="employee_id" class="form-select form-select-sm" style="max-width: 18rem" required>
                                        <option value="">Select employee...</option>
                                        @foreach ($employees as $e) <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_code }})</option> @endforeach
                                    </select>
                                    <input type="date" name="assigned_on" value="{{ \App\Support\HrClock::today()->format('Y-m-d') }}" class="form-control form-control-sm" style="max-width: 10rem" required>
                                    <button class="btn btn-sm btn-brand">Assign</button>
                                </form>
                            </td></tr>
                            <tr class="collapse" id="ret-{{ $a->id }}"><td colspan="5" class="px-3 pb-3">
                                <form method="POST" action="{{ route('admin.hr.assets.return', $a) }}" class="d-flex flex-wrap gap-2">
                                    @csrf
                                    <input type="date" name="returned_on" value="{{ \App\Support\HrClock::today()->format('Y-m-d') }}" class="form-control form-control-sm" style="max-width: 10rem" required>
                                    <select name="new_status" class="form-select form-select-sm" style="max-width: 10rem">
                                        <option value="available">Available</option><option value="repair">Needs repair</option><option value="retired">Retire</option>
                                    </select>
                                    <input name="return_note" class="form-control form-control-sm" style="max-width: 18rem" placeholder="Condition / note">
                                    <button class="btn btn-sm btn-outline-secondary">Mark returned</button>
                                </form>
                            </td></tr>
                            <tr class="collapse" id="edit-{{ $a->id }}"><td colspan="5" class="px-3 pb-3">
                                <form method="POST" action="{{ route('admin.hr.assets.update', $a) }}" class="row g-2">
                                    @csrf @method('PUT')
                                    {!! $fields($a) !!}
                                    @if ($a->status !== 'assigned')
                                        <div class="col-md-3">
                                            <select name="status" class="form-select form-select-sm">
                                                @foreach (['available', 'repair', 'retired'] as $st) <option value="{{ $st }}" @selected($a->status === $st)>{{ \App\Models\Hr\Asset::STATUSES[$st] }}</option> @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100">Save</button></div>
                                </form>
                            </td></tr>
                        @endif
                        <tr class="collapse" id="hist-{{ $a->id }}"><td colspan="5" class="px-3 pb-3 fs-xs">
                            @forelse ($a->assignments as $h)
                                <div>{{ $h->employee?->name }}: {{ $h->assigned_on->format('d M Y') }} &rarr; {{ $h->returned_on?->format('d M Y') ?? 'now' }} {{ $h->return_note ? '('.$h->return_note.')' : '' }}</div>
                            @empty
                                <span class="text-secondary">Never assigned.</span>
                            @endforelse
                        </td></tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">No assets yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $assets->links() }}</div>
</x-layouts.app>
