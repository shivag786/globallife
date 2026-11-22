<x-layouts.app title="Attendance" heading="Attendance - {{ $date->format('d M Y, l') }}">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" max="{{ \App\Support\HrClock::today()->format('Y-m-d') }}" class="form-control form-control-sm">
            <select name="department" class="form-select form-select-sm" style="width: 12rem">
                <option value="">All departments</option>
                @foreach ($departments as $d)
                    <option value="{{ $d->id }}" @selected((string) request('department') === (string) $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Show</button>
        </form>
        <div class="d-flex gap-2 small">
            <span class="badge text-bg-success">{{ $records->whereIn('status', ['present', 'half_day'])->count() }} present</span>
            <span class="badge text-bg-warning">{{ $records->where('is_late', true)->count() }} late</span>
            <span class="badge text-bg-info">{{ $leaves->count() }} on leave</span>
        </div>
    </div>

    @if ($holiday)
        <div class="alert alert-info small">{{ $date->format('d M') }} is a holiday: <strong>{{ $holiday }}</strong>.</div>
    @endif

    <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-100 fs-sm">
                <thead class="bg-body-tertiary text-secondary">
                    <tr>
                        <th class="px-3 py-3">Employee</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">In</th>
                        <th class="px-3 py-3">Out</th>
                        <th class="px-3 py-3">Worked</th>
                        <th class="px-3 py-3">Note</th>
                        @if ($canEdit) <th class="px-3 py-3"></th> @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $e)
                        @php $r = $records->get($e->id); $l = $leaves->get($e->id); @endphp
                        <tr class="border-top">
                            <td class="px-3 py-2">
                                <strong>{{ $e->name }}</strong>
                                <div class="fs-xs text-secondary">{{ $e->employee_code }} &middot; {{ $e->department?->name }}</div>
                            </td>
                            <td class="px-3 py-2">
                                @if ($r)
                                    @include('hr.partials.badge', ['status' => $r->status])
                                    @if ($r->is_late) @include('hr.partials.badge', ['status' => 'late']) @endif
                                @elseif ($l)
                                    @include('hr.partials.badge', ['status' => 'leave', 'label' => $l->leaveType->code.' leave'.($l->is_half_day ? ' (half)' : '')])
                                @else
                                    <span class="text-secondary fs-xs">Not marked</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $r?->check_in_at?->format('h:i A') ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r?->check_out_at?->format('h:i A') ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $r?->workedLabel() ?? '-' }}</td>
                            <td class="px-3 py-2 fs-xs text-secondary">{{ $r?->note }} @if ($r && $r->source !== 'self') <em>({{ $r->source }})</em> @endif</td>
                            @if ($canEdit)
                                <td class="px-3 py-2 text-end"><button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#att-{{ $e->id }}">Mark / edit</button></td>
                            @endif
                        </tr>
                        @if ($canEdit)
                            <tr class="collapse" id="att-{{ $e->id }}">
                                <td colspan="7" class="px-3 pb-3">
                                    <form method="POST" action="{{ route('admin.hr.attendance.store') }}" class="row g-2 align-items-end">
                                        @csrf
                                        <input type="hidden" name="employee_id" value="{{ $e->id }}">
                                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                        <div class="col-md-2">
                                            <label class="form-label small">Check-in</label>
                                            <input type="time" name="check_in" value="{{ $r?->check_in_at?->format('H:i') }}" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">Check-out</label>
                                            <input type="time" name="check_out" value="{{ $r?->check_out_at?->format('H:i') }}" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">Status</label>
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="">Auto (from times)</option>
                                                @foreach (\App\Models\Hr\Attendance::STATUSES as $k => $lbl)
                                                    <option value="{{ $k }}">{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Note</label>
                                            <input name="note" value="{{ $r?->note }}" class="form-control form-control-sm" placeholder="Reason for manual entry">
                                        </div>
                                        <div class="col-md-2"><button class="btn btn-sm btn-brand w-100">Save</button></div>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-secondary">No employees.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
