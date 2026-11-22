<x-layouts.app title="My Profile" heading="My Profile">
    @php
        $e = $employee;
        $row = fn ($label, $value) => '<div class="col-sm-6 col-lg-4"><div class="text-secondary fs-xs text-uppercase">'.e($label).'</div><div class="fw-medium">'.($value !== null && $value !== '' ? e($value) : '<span class="text-secondary">-</span>').'</div></div>';
        $money = fn ($v) => 'Rs. '.number_format((float) $v, 2);
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap gap-4 align-items-center">
            @if ($e->photo_path)
                <img src="{{ route('hr.files.photo', $e) }}" alt="" class="rounded-circle object-fit-cover border" style="width:84px;height:84px">
            @else
                <div class="rounded-circle bg-body-tertiary border d-flex align-items-center justify-content-center fw-bold fs-3 text-secondary" style="width:84px;height:84px">{{ strtoupper(substr($e->name, 0, 1)) }}</div>
            @endif
            <div>
                <h2 class="h4 fw-bold mb-1">{{ $e->name }}</h2>
                <div class="text-secondary small"><span class="font-monospace">{{ $e->employee_code }}</span> &middot; {{ $e->designation?->name }} &middot; {{ $e->department?->name }}</div>
                <div class="mt-2">@include('hr.partials.badge', ['status' => $e->employment_status, 'label' => $e->statusLabel()])</div>
            </div>
        </div>
    </div>

    <p class="small text-secondary">To change any of these details, please contact HR.</p>

    <div class="row g-4">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <x-dash-card title="Job details" icon="briefcase">
                <div class="row g-3">
                    {!! $row('Employee ID (login)', $e->employee_code) !!}
                    {!! $row('Email', $e->user->email) !!}
                    {!! $row('Mobile', $e->user->mobile) !!}
                    {!! $row('Employment type', $e->typeLabel()) !!}
                    {!! $row('Joining date', $e->joining_date->format('d M Y')) !!}
                    {!! $row('Reporting manager', $e->reportingManager?->name) !!}
                    {!! $row('Shift', $e->effectiveShift()?->label()) !!}
                    {!! $row('Location', $e->city?->name) !!}
                    {!! $row('Probation ends', $e->probation_end_date?->format('d M Y')) !!}
                </div>
            </x-dash-card>

            <x-dash-card title="Personal details" icon="identification">
                <div class="row g-3">
                    {!! $row('Date of birth', $e->date_of_birth?->format('d M Y')) !!}
                    {!! $row('Gender', $e->gender ? ucfirst($e->gender) : null) !!}
                    {!! $row('Blood group', $e->blood_group) !!}
                    {!! $row('Emergency contact', $e->emergency_contact_name) !!}
                    {!! $row('Relation', $e->emergency_contact_relation) !!}
                    {!! $row('Emergency phone', $e->emergency_contact_phone) !!}
                    <div class="col-md-6"><div class="text-secondary fs-xs text-uppercase">Current address</div><div class="fw-medium" style="white-space:pre-line">{{ $e->current_address ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-secondary fs-xs text-uppercase">Permanent address</div><div class="fw-medium" style="white-space:pre-line">{{ $e->permanent_address ?: '-' }}</div></div>
                </div>
            </x-dash-card>

            <x-dash-card title="Bank & statutory" icon="lock">
                <div class="row g-3">
                    {!! $row('Bank', $e->bank_name) !!}
                    {!! $row('Account number', $e->bank_account_number ? 'XXXX'.substr($e->bank_account_number, -4) : null) !!}
                    {!! $row('IFSC', $e->bank_ifsc) !!}
                    {!! $row('PAN', $e->pan_number) !!}
                    {!! $row('Aadhaar', $e->aadhaar_number ? 'XXXX XXXX '.substr($e->aadhaar_number, -4) : null) !!}
                    {!! $row('PF UAN', $e->uan_number) !!}
                </div>
            </x-dash-card>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <x-dash-card title="My documents" icon="inbox">
                @forelse ($e->documents as $doc)
                    <div class="border-bottom py-2 small">
                        <a href="{{ route('hr.files.document', $doc) }}" target="_blank" class="fw-medium">{{ $doc->title }}</a>
                        <div class="fs-xs text-secondary">{{ $doc->created_at->format('d M Y') }}</div>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">No documents shared yet.</p>
                @endforelse
            </x-dash-card>

            <x-dash-card title="Salary structure" icon="rupee">
                @if ($e->salary)
                    @foreach (\App\Models\Hr\EmployeeSalary::EARNINGS as $key => $label)
                        @if ($e->salary->{$key} > 0)
                            <div class="d-flex justify-content-between small"><span>{{ $label }}</span><span>{{ $money($e->salary->{$key}) }}</span></div>
                        @endif
                    @endforeach
                    <div class="d-flex justify-content-between fw-bold border-top mt-2 pt-2"><span>Monthly gross</span><span>{{ $money($e->salary->gross()) }}</span></div>
                @else
                    <p class="small text-secondary mb-0">Not set up yet.</p>
                @endif
                @foreach ($e->advances as $adv)
                    <div class="fs-xs text-warning-emphasis mt-2">Advance outstanding: {{ $money($adv->balance) }} (EMI {{ $money($adv->monthly_deduction) }})</div>
                @endforeach
            </x-dash-card>

            <x-dash-card title="Company assets with me" icon="briefcase">
                @forelse ($e->assets as $asset)
                    <div class="border-bottom py-2 small">
                        <strong>{{ $asset->name }}</strong> <span class="font-monospace text-secondary">{{ $asset->asset_tag }}</span>
                        <div class="fs-xs text-secondary">{{ $asset->category }} &middot; since {{ $asset->assigned_on?->format('d M Y') }}</div>
                    </div>
                @empty
                    <p class="small text-secondary mb-0">None.</p>
                @endforelse
            </x-dash-card>
        </div>
    </div>
</x-layouts.app>
