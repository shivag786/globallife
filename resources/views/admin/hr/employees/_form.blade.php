@csrf
@isset($employee) @if ($employee) @method('PUT') @endif @endisset

@php
    $e = $employee ?? null;
    $u = $e?->user;
    $s = $e?->salary;
    $date = fn ($v) => $v ? $v->format('Y-m-d') : '';
    $sal = fn (string $key) => old("salary.$key", $s?->{$key});
@endphp

<x-member-form :sections="[
    ['id' => 'section-account', 'title' => 'Login & contact', 'icon' => 'user'],
    ['id' => 'section-job', 'title' => 'Job details', 'icon' => 'briefcase'],
    ['id' => 'section-personal', 'title' => 'Personal details', 'icon' => 'identification'],
    ['id' => 'section-emergency', 'title' => 'Emergency contact', 'icon' => 'phone'],
    ['id' => 'section-bank', 'title' => 'Bank & statutory', 'icon' => 'lock'],
    ['id' => 'section-salary', 'title' => 'Salary structure', 'icon' => 'rupee'],
]">

    <x-form-section id="section-account" title="Login & contact" icon="user"
                    summary="{{ $e ? 'Their name and the email they log in with.' : 'The Employee ID and password are generated and emailed to this address.' }}">
        <div class="row g-4">
            <div class="col-md-6">
                <x-forms.input name="name" label="Full name" :value="$u?->name" required autocomplete="off" />
            </div>
            <div class="col-md-6">
                <x-forms.input name="email" type="email" label="Email (login + credentials are sent here)" :value="$u?->email" required autocomplete="off" />
            </div>
            <div class="col-md-6">
                <x-forms.mobile name="mobile" label="Mobile" :value="$u?->mobile ?? ''" />
            </div>
            @unless ($e)
                <div class="col-md-6">
                    <x-forms.input name="password" type="text" label="Password (optional)" autocomplete="off"
                                   help="Leave blank to auto-generate a secure password. Either way it is emailed to the employee." />
                </div>
                <div class="col-12">
                    <div class="alert alert-info small mb-0">
                        Employee ID will be generated automatically in the format <strong>{{ $nextCodePrefix }}0001</strong>.
                        The employee can log in with either the Employee ID or the email.
                    </div>
                </div>
            @endunless
            <div class="col-md-6">
                <label class="form-label small fw-medium" for="photo">Photo</label>
                <input type="file" name="photo" id="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if ($e?->photo_path)
                    <div class="form-text">A photo is on file. Upload a new one to replace it.</div>
                @endif
            </div>
        </div>
    </x-form-section>

    <x-form-section id="section-job" title="Job details" icon="briefcase" summary="Department, role, shift and who they report to.">
        <div class="row g-4">
            <div class="col-md-6">
                <x-forms.input name="department_id" label="Department" as="select">
                    <option value="">- None -</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->id }}" @selected((string) old('department_id', $e?->department_id) === (string) $d->id)>{{ $d->name }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-6">
                <x-forms.input name="designation_id" label="Designation" as="select">
                    <option value="">- None -</option>
                    @foreach ($designations as $d)
                        <option value="{{ $d->id }}" @selected((string) old('designation_id', $e?->designation_id) === (string) $d->id)>
                            {{ $d->name }}{{ $d->department ? ' ('.$d->department->name.')' : '' }}
                        </option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-6">
                <x-forms.input name="reporting_manager_id" label="Reporting manager" as="select"
                               help="Their manager can approve their leave from the employee portal.">
                    <option value="">- None -</option>
                    @foreach ($managers as $m)
                        <option value="{{ $m->id }}" @selected((string) old('reporting_manager_id', $e?->reporting_manager_id) === (string) $m->id)>
                            {{ $m->name }} ({{ $m->employee_code }})
                        </option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-6">
                <x-forms.input name="shift_id" label="Shift" as="select" help="Blank = the default shift.">
                    <option value="">Default shift</option>
                    @foreach ($shifts as $sh)
                        <option value="{{ $sh->id }}" @selected((string) old('shift_id', $e?->shift_id) === (string) $sh->id)>{{ $sh->label() }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-6">
                <x-forms.input name="employment_type" label="Employment type" as="select" required>
                    @foreach (\App\Models\Hr\Employee::EMPLOYMENT_TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('employment_type', $e?->employment_type ?? 'full_time') === $key)>{{ $label }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-6">
                <x-forms.input name="city_id" label="Branch / location" as="select">
                    <option value="">- None -</option>
                    @foreach ($cities as $c)
                        <option value="{{ $c->id }}" @selected((string) old('city_id', $e?->city_id) === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-4">
                <x-forms.input name="joining_date" type="date" label="Joining date" :value="$date($e?->joining_date)" required />
            </div>
            <div class="col-md-4">
                <x-forms.input name="probation_end_date" type="date" label="Probation ends" :value="$date($e?->probation_end_date)" />
            </div>
            <div class="col-md-4">
                <x-forms.input name="confirmation_date" type="date" label="Confirmation date" :value="$date($e?->confirmation_date)" />
            </div>
        </div>
    </x-form-section>

    <x-form-section id="section-personal" title="Personal details" icon="identification" :open="false">
        <div class="row g-4">
            <div class="col-md-4">
                <x-forms.input name="date_of_birth" type="date" label="Date of birth" :value="$date($e?->date_of_birth)" />
            </div>
            <div class="col-md-4">
                <x-forms.input name="gender" label="Gender" as="select">
                    <option value="">-</option>
                    @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $k => $l)
                        <option value="{{ $k }}" @selected(old('gender', $e?->gender) === $k)>{{ $l }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-2">
                <x-forms.input name="blood_group" label="Blood group" as="select">
                    <option value="">-</option>
                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group', $e?->blood_group) === $bg)>{{ $bg }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-2">
                <x-forms.input name="marital_status" label="Marital status" as="select">
                    <option value="">-</option>
                    @foreach (['single' => 'Single', 'married' => 'Married', 'other' => 'Other'] as $k => $l)
                        <option value="{{ $k }}" @selected(old('marital_status', $e?->marital_status) === $k)>{{ $l }}</option>
                    @endforeach
                </x-forms.input>
            </div>
            <div class="col-md-6">
                <x-forms.input name="current_address" label="Current address" as="textarea" :value="$e?->current_address" />
            </div>
            <div class="col-md-6">
                <x-forms.input name="permanent_address" label="Permanent address" as="textarea" :value="$e?->permanent_address" />
            </div>
        </div>
    </x-form-section>

    <x-form-section id="section-emergency" title="Emergency contact" icon="phone" :open="false">
        <div class="row g-4">
            <div class="col-md-4"><x-forms.input name="emergency_contact_name" label="Name" :value="$e?->emergency_contact_name" /></div>
            <div class="col-md-4"><x-forms.input name="emergency_contact_relation" label="Relation" :value="$e?->emergency_contact_relation" /></div>
            <div class="col-md-4"><x-forms.input name="emergency_contact_phone" label="Phone" :value="$e?->emergency_contact_phone" /></div>
        </div>
    </x-form-section>

    <x-form-section id="section-bank" title="Bank & statutory" icon="lock" :open="false" summary="Where salary is paid, plus PAN / Aadhaar / UAN / ESI numbers.">
        <div class="row g-4">
            <div class="col-md-4"><x-forms.input name="bank_name" label="Bank name" :value="$e?->bank_name" /></div>
            <div class="col-md-4"><x-forms.input name="bank_account_name" label="Account holder name" :value="$e?->bank_account_name" /></div>
            <div class="col-md-4"><x-forms.input name="bank_account_number" label="Account number" :value="$e?->bank_account_number" /></div>
            <div class="col-md-4"><x-forms.input name="bank_ifsc" label="IFSC" :value="$e?->bank_ifsc" /></div>
            <div class="col-md-4"><x-forms.input name="upi_id" label="UPI ID" :value="$e?->upi_id" /></div>
            <div class="col-md-4"><x-forms.input name="pan_number" label="PAN" :value="$e?->pan_number" /></div>
            <div class="col-md-4"><x-forms.input name="aadhaar_number" label="Aadhaar number" :value="$e?->aadhaar_number" /></div>
            <div class="col-md-4"><x-forms.input name="uan_number" label="PF UAN" :value="$e?->uan_number" /></div>
            <div class="col-md-4"><x-forms.input name="esi_number" label="ESI number" :value="$e?->esi_number" /></div>
        </div>
    </x-form-section>

    <x-form-section id="section-salary" title="Salary structure" icon="rupee" :open="! $e"
                    summary="Monthly amounts. Leave Basic empty to keep this employee out of payroll.">
        <div class="row g-4">
            @foreach (\App\Models\Hr\EmployeeSalary::EARNINGS as $key => $label)
                <div class="col-md-4">
                    <label class="form-label small fw-medium" for="salary_{{ $key }}">{{ $label }} (monthly)</label>
                    <input type="number" step="0.01" min="0" name="salary[{{ $key }}]" id="salary_{{ $key }}" value="{{ $sal($key) }}"
                           class="form-control @error("salary.$key") is-invalid @enderror" data-salary-part>
                    @error("salary.$key") <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endforeach
            <div class="col-md-4">
                <label class="form-label small fw-medium">Monthly gross</label>
                <div class="form-control bg-body-tertiary fw-semibold" data-salary-gross>-</div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-medium" for="salary_professional_tax">Professional tax (monthly)</label>
                <input type="number" step="0.01" min="0" name="salary[professional_tax]" id="salary_professional_tax"
                       value="{{ $sal('professional_tax') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-medium" for="salary_tds">TDS (monthly)</label>
                <input type="number" step="0.01" min="0" name="salary[tds]" id="salary_tds" value="{{ $sal('tds') }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex flex-column justify-content-end gap-2">
                <input type="hidden" name="salary[pf_enabled]" value="0">
                <label class="form-check">
                    <input type="checkbox" class="form-check-input" name="salary[pf_enabled]" value="1" @checked(old('salary.pf_enabled', $s?->pf_enabled))>
                    <span class="form-check-label small">Deduct PF (12% of basic, capped at Rs. 15,000)</span>
                </label>
                <input type="hidden" name="salary[esi_enabled]" value="0">
                <label class="form-check">
                    <input type="checkbox" class="form-check-input" name="salary[esi_enabled]" value="1" @checked(old('salary.esi_enabled', $s?->esi_enabled))>
                    <span class="form-check-label small">Deduct ESI (0.75%, gross up to Rs. 21,000)</span>
                </label>
            </div>
        </div>
    </x-form-section>

    <div class="member-form-actions">
        <button type="submit" class="btn btn-brand">{{ $e ? 'Save changes' : 'Create employee & email login' }}</button>
        <a href="{{ $e ? route('admin.hr.employees.show', $e) : route('admin.hr.employees.index') }}" class="btn btn-link text-secondary">Cancel</a>
    </div>
</x-member-form>

<script>
    (function () {
        const parts = document.querySelectorAll('[data-salary-part]');
        const out = document.querySelector('[data-salary-gross]');
        const update = () => {
            let total = 0;
            parts.forEach((p) => total += parseFloat(p.value) || 0);
            out.textContent = total > 0 ? 'Rs. ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-';
        };
        parts.forEach((p) => p.addEventListener('input', update));
        update();
    })();
</script>
