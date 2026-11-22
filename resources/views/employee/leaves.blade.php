<x-layouts.app title="Leaves" heading="Leaves & Holidays">
    <div class="d-grid grid-cols-2 grid-cols-md-4 gap-4 mb-4">
        @foreach ($balances as $b)
            <x-stat-tile :label="$b['type']->name.' ('.$b['type']->code.')'" :value="$b['available']" icon="calendar" color="sky"
                         :hint="'available · '.$b['balance']->used.' used of '.$b['balance']->total().($b['pending'] ? ' · '.$b['pending'].' pending' : '')" />
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Apply for leave</div>
                <form method="POST" action="{{ route('employee.leaves.store') }}" class="card-body d-flex flex-column gap-3" id="leave-form">
                    @csrf
                    <div>
                        <label class="form-label small fw-medium">Leave type</label>
                        <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                            @foreach ($types as $t)
                                <option value="{{ $t->id }}" @selected(old('leave_type_id') == $t->id)>{{ $t->name }}{{ $t->is_paid ? '' : ' (unpaid)' }}</option>
                            @endforeach
                        </select>
                        @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <label class="form-check small">
                        <input type="checkbox" name="is_half_day" value="1" class="form-check-input" data-half-day @checked(old('is_half_day'))> Half day
                    </label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-medium">From</label>
                            <input type="date" name="from_date" value="{{ old('from_date') }}" class="form-control @error('from_date') is-invalid @enderror" required>
                        </div>
                        <div class="col-6" data-to-date>
                            <label class="form-label small fw-medium">To</label>
                            <input type="date" name="to_date" value="{{ old('to_date') }}" class="form-control @error('to_date') is-invalid @enderror">
                        </div>
                        @error('from_date') <div class="col-12 text-danger small">{{ $message }}</div> @enderror
                        @error('to_date') <div class="col-12 text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label small fw-medium">Reason</label>
                        <textarea name="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea>
                    </div>
                    <p class="fs-xs text-secondary mb-0">Weekly offs and holidays inside the dates are not counted.</p>
                    <button class="btn btn-brand">Submit request</button>
                </form>
            </div>

            <x-dash-card title="Holidays {{ \App\Support\HrClock::now()->year }}" icon="sparkles">
                @forelse ($holidays as $h)
                    <div class="d-flex justify-content-between border-bottom py-1 small {{ $h->date->lt(\App\Support\HrClock::today()) && ! $h->date->isSameDay(\App\Support\HrClock::today()) ? 'text-secondary' : '' }}">
                        <span>{{ $h->name }}</span><span>{{ $h->date->format('D, d M') }}</span>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">No holidays listed.</p>
                @endforelse
            </x-dash-card>
        </div>

        <div class="col-lg-8">
            <div class="bg-body rounded-2 shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-100 fs-sm">
                        <thead class="bg-body-tertiary text-secondary">
                            <tr><th class="px-3 py-3">Type</th><th class="px-3 py-3">Dates</th><th class="px-3 py-3">Days</th><th class="px-3 py-3">Status</th><th class="px-3 py-3"></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $r)
                                <tr class="border-top align-top">
                                    <td class="px-3 py-2">{{ $r->leaveType->name }}<div class="fs-xs text-secondary">{{ \Illuminate\Support\Str::limit($r->reason, 60) }}</div></td>
                                    <td class="px-3 py-2">{{ $r->periodLabel() }}</td>
                                    <td class="px-3 py-2">{{ $r->days }}</td>
                                    <td class="px-3 py-2">
                                        @include('hr.partials.badge', ['status' => $r->status])
                                        @if ($r->review_note) <div class="fs-xs text-secondary">{{ $r->review_note }}</div> @endif
                                    </td>
                                    <td class="px-3 py-2 text-end">
                                        @if ($r->status === 'pending' || ($r->status === 'approved' && $r->from_date->isAfter(\App\Support\HrClock::today())))
                                            <form method="POST" action="{{ route('employee.leaves.cancel', $r) }}" onsubmit="return confirm('Cancel this leave request?')">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-sm btn-link text-danger p-0">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-secondary">You haven't applied for leave yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $requests->links() }}</div>
        </div>
    </div>

    <script>
        (function () {
            const half = document.querySelector('[data-half-day]');
            const to = document.querySelector('[data-to-date]');
            const sync = () => { to.style.display = half.checked ? 'none' : ''; };
            half.addEventListener('change', sync);
            sync();
        })();
    </script>
</x-layouts.app>
