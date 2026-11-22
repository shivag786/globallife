<x-layouts.app title="{{ $employee->name }}" heading="Employee Profile">
    @php
        $row = fn ($label, $value) => '<div class="col-sm-6 col-lg-4"><div class="text-secondary fs-xs text-uppercase">'.e($label).'</div><div class="fw-medium">'.($value !== null && $value !== '' ? e($value) : '<span class="text-secondary">-</span>').'</div></div>';
        $money = fn ($v) => 'Rs. '.number_format((float) $v, 2);
    @endphp

    @if (session('credentials'))
        <div class="alert alert-warning">
            <strong>Share these login details with the employee:</strong><br>
            Employee ID: <span class="font-monospace">{{ session('credentials')['code'] }}</span> &middot;
            Password: <span class="font-monospace fw-bold">{{ session('credentials')['password'] }}</span>
            <div class="small mt-1">This password is shown only once.</div>
        </div>
    @endif

    {{-- Header --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap gap-4 align-items-center">
            @if ($employee->photo_path)
                <img src="{{ route('hr.files.photo', $employee) }}" alt="" class="rounded-circle object-fit-cover border" style="width:84px;height:84px">
            @else
                <div class="rounded-circle bg-body-tertiary border d-flex align-items-center justify-content-center fw-bold fs-3 text-secondary" style="width:84px;height:84px">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>
            @endif
            <div class="flex-grow-1 min-w-0">
                <h2 class="h4 fw-bold mb-1">{{ $employee->name }}</h2>
                <div class="text-secondary small">
                    <span class="font-monospace">{{ $employee->employee_code }}</span>
                    &middot; {{ $employee->designation?->name ?? 'No designation' }}
                    &middot; {{ $employee->department?->name ?? 'No department' }}
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @include('hr.partials.badge', ['status' => $employee->employment_status, 'label' => $employee->statusLabel()])
                    <span class="badge rounded-pill text-bg-light border">{{ $employee->typeLabel() }}</span>
                    <span class="badge rounded-pill text-bg-light border">Joined {{ $employee->joining_date->format('d M Y') }}</span>
                    @if ($employee->exit_date)
                        <span class="badge rounded-pill text-bg-light border">Exit {{ $employee->exit_date->format('d M Y') }}</span>
                    @endif
                </div>
            </div>
            @can('employees.edit')
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.hr.employees.edit', $employee) }}" class="btn btn-brand btn-sm">Edit details</a>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#password-box">Reset password</button>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#status-box">Change status</button>
                </div>
            @endcan
        </div>

        @can('employees.edit')
            <div class="collapse border-top" id="password-box">
                <form method="POST" action="{{ route('admin.hr.employees.password', $employee) }}" class="card-body row g-3 align-items-end">
                    @csrf @method('PUT')
                    <div class="col-md-5">
                        <label class="form-label small fw-medium">New password</label>
                        <input type="text" name="password" class="form-control" autocomplete="off" placeholder="Leave blank to auto-generate">
                    </div>
                    <div class="col-md-7">
                        <button class="btn btn-warning btn-sm">Reset &amp; email new password</button>
                        <span class="small text-secondary ms-2">The Employee ID and new password are emailed to {{ $employee->user->email }}.</span>
                    </div>
                </form>
            </div>
            <div class="collapse border-top" id="status-box">
                <form method="POST" action="{{ route('admin.hr.employees.status', $employee) }}" class="card-body row g-3 align-items-end">
                    @csrf @method('PATCH')
                    <div class="col-md-3">
                        <label class="form-label small fw-medium">Status</label>
                        <select name="employment_status" class="form-select">
                            @foreach (\App\Models\Hr\Employee::STATUSES as $k => $l)
                                <option value="{{ $k }}" @selected($employee->employment_status === $k)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-medium">Last working day</label>
                        <input type="date" name="exit_date" class="form-control" value="{{ $employee->exit_date?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-medium">Reason</label>
                        <input type="text" name="exit_reason" class="form-control" value="{{ $employee->exit_reason }}">
                    </div>
                    <div class="col-md-2"><button class="btn btn-sm btn-outline-danger w-100">Save status</button></div>
                    <div class="col-12 small text-secondary">Resigned / Terminated blocks the employee's login. On Notice keeps it open until they leave.</div>
                </form>
            </div>
        @endcan
    </div>

    <div class="row g-4">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <x-dash-card title="Job & contact" icon="briefcase">
                <div class="row g-3">
                    {!! $row('Email', $employee->user->email) !!}
                    {!! $row('Mobile', $employee->user->mobile) !!}
                    {!! $row('Reporting manager', $employee->reportingManager?->name) !!}
                    {!! $row('Shift', $employee->effectiveShift()?->label()) !!}
                    {!! $row('Branch / location', $employee->city?->name) !!}
                    {!! $row('Probation ends', $employee->probation_end_date?->format('d M Y')) !!}
                    {!! $row('Confirmed on', $employee->confirmation_date?->format('d M Y')) !!}
                    {!! $row('Team size', $employee->directReports->count() ?: null) !!}
                    {!! $row('Exit reason', $employee->exit_reason) !!}
                </div>
            </x-dash-card>

            <x-dash-card title="Personal" icon="identification">
                <div class="row g-3">
                    {!! $row('Date of birth', $employee->date_of_birth?->format('d M Y')) !!}
                    {!! $row('Gender', $employee->gender ? ucfirst($employee->gender) : null) !!}
                    {!! $row('Blood group', $employee->blood_group) !!}
                    {!! $row('Marital status', $employee->marital_status ? ucfirst($employee->marital_status) : null) !!}
                    {!! $row('Emergency contact', trim($employee->emergency_contact_name.' '.($employee->emergency_contact_relation ? '('.$employee->emergency_contact_relation.')' : ''))) !!}
                    {!! $row('Emergency phone', $employee->emergency_contact_phone) !!}
                    <div class="col-md-6"><div class="text-secondary fs-xs text-uppercase">Current address</div><div class="fw-medium" style="white-space:pre-line">{{ $employee->current_address ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-secondary fs-xs text-uppercase">Permanent address</div><div class="fw-medium" style="white-space:pre-line">{{ $employee->permanent_address ?: '-' }}</div></div>
                </div>
            </x-dash-card>

            <x-dash-card title="Bank & statutory" icon="lock">
                <div class="row g-3">
                    {!! $row('Bank', $employee->bank_name) !!}
                    {!! $row('Account holder', $employee->bank_account_name) !!}
                    {!! $row('Account number', $employee->bank_account_number) !!}
                    {!! $row('IFSC', $employee->bank_ifsc) !!}
                    {!! $row('UPI', $employee->upi_id) !!}
                    {!! $row('PAN', $employee->pan_number) !!}
                    {!! $row('Aadhaar', $employee->aadhaar_number ? 'XXXX XXXX '.substr($employee->aadhaar_number, -4) : null) !!}
                    {!! $row('PF UAN', $employee->uan_number) !!}
                    {!! $row('ESI number', $employee->esi_number) !!}
                </div>
            </x-dash-card>

            <x-dash-card title="Documents" icon="inbox">
                <ul class="list-unstyled mb-3">
                    @forelse ($employee->documents as $doc)
                        <li class="d-flex justify-content-between align-items-center border-bottom py-2 gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('hr.files.document', $doc) }}" target="_blank" class="fw-medium">{{ $doc->title }}</a>
                                <div class="fs-xs text-secondary text-truncate">{{ $doc->original_name }} &middot; {{ $doc->created_at->format('d M Y') }}</div>
                            </div>
                            @can('employees.edit')
                                <form method="POST" action="{{ route('admin.hr.employees.documents.destroy', [$employee, $doc]) }}" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0">Delete</button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="text-secondary small">No documents uploaded yet.</li>
                    @endforelse
                </ul>
                @can('employees.edit')
                    <form method="POST" action="{{ route('admin.hr.employees.documents.store', $employee) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-5"><input type="text" name="title" class="form-control form-control-sm" placeholder="Title, e.g. Offer letter" required></div>
                        <div class="col-md-5"><input type="file" name="document" class="form-control form-control-sm" required></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100">Upload</button></div>
                        <div class="col-12 fs-xs text-secondary">PDF, image or Word, up to 5 MB.</div>
                    </form>
                @endcan
            </x-dash-card>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <x-dash-card title="This month" subtitle="{{ \App\Support\HrClock::now()->format('F Y') }}" icon="calendar">
                <div class="d-grid gap-2 small" style="grid-template-columns: 1fr 1fr">
                    <div>Present: <strong>{{ $monthSummary['present'] }}</strong></div>
                    <div>Absent: <strong>{{ $monthSummary['absent'] }}</strong></div>
                    <div>Leave: <strong>{{ $monthSummary['leave'] + $monthSummary['unpaid_leave'] }}</strong></div>
                    <div>Late: <strong>{{ $monthSummary['late'] }}</strong></div>
                </div>
            </x-dash-card>

            <x-dash-card title="Leave balance" subtitle="{{ \App\Support\HrClock::now()->year }}" icon="calendar">
                @forelse ($balances as $b)
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span>{{ $b['type']->name }}</span>
                        <span><strong>{{ $b['balance']->remaining() }}</strong> / {{ $b['balance']->total() }}</span>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">No leave types with a quota.</p>
                @endforelse
            </x-dash-card>

            <x-dash-card title="Salary" subtitle="Monthly structure" icon="rupee">
                @if ($employee->salary)
                    @foreach (\App\Models\Hr\EmployeeSalary::EARNINGS as $key => $label)
                        <div class="d-flex justify-content-between small"><span>{{ $label }}</span><span>{{ $money($employee->salary->{$key}) }}</span></div>
                    @endforeach
                    <div class="d-flex justify-content-between fw-bold border-top mt-2 pt-2"><span>Gross</span><span>{{ $money($employee->salary->gross()) }}</span></div>
                    <div class="fs-xs text-secondary mt-1">
                        PF {{ $employee->salary->pf_enabled ? 'on' : 'off' }} &middot; ESI {{ $employee->salary->esi_enabled ? 'on' : 'off' }}
                        &middot; PT {{ $money($employee->salary->professional_tax) }} &middot; TDS {{ $money($employee->salary->tds) }}
                    </div>
                @else
                    <p class="small text-secondary mb-0">No salary structure yet - this employee is left out of payroll.</p>
                @endif
            </x-dash-card>

            <x-dash-card title="Assets held" icon="briefcase">
                @forelse ($employee->assets as $asset)
                    <div class="small border-bottom py-1">{{ $asset->name }} <span class="text-secondary font-monospace">{{ $asset->asset_tag }}</span></div>
                @empty
                    <p class="small text-secondary mb-0">None.</p>
                @endforelse
            </x-dash-card>

            <x-dash-card title="Recent leave" icon="calendar">
                @forelse ($recentLeaves as $l)
                    <div class="small border-bottom py-1 d-flex justify-content-between gap-2">
                        <span>{{ $l->leaveType->code }} &middot; {{ $l->periodLabel() }}</span>
                        @include('hr.partials.badge', ['status' => $l->status])
                    </div>
                @empty
                    <p class="small text-secondary mb-0">None.</p>
                @endforelse
            </x-dash-card>

            <x-dash-card title="Payslips" icon="rupee">
                @forelse ($payslips as $p)
                    <div class="small border-bottom py-1 d-flex justify-content-between">
                        <a href="{{ route('admin.hr.payslips.show', $p) }}" target="_blank">{{ $p->run->label() }}</a>
                        <span>{{ $money($p->net_pay) }}</span>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">None yet.</p>
                @endforelse
                @if ($employee->advances->where('status', 'active')->isNotEmpty())
                    <div class="fs-xs text-warning-emphasis mt-2">
                        Advance outstanding: {{ $money($employee->advances->where('status', 'active')->sum('balance')) }}
                    </div>
                @endif
            </x-dash-card>
        </div>
    </div>
</x-layouts.app>
