<x-layouts.app title="Shifts & HR Settings" heading="Shifts & HR Settings">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-semibold">Shifts</div>
                <div class="card-body">
                    <p class="small text-secondary">
                        An employee who checks in after <em>start time + grace</em> is marked late. At check-out, worked time of at least
                        the <em>full day</em> minutes is Present, at least the <em>half day</em> minutes is Half Day, and less is Absent.
                    </p>
                    @foreach ($shifts as $shift)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $shift->label() }}</strong>
                                    @if ($shift->is_default) <span class="badge text-bg-success">Default</span> @endif
                                    <div class="fs-xs text-secondary">
                                        {{ $shift->employees_count }} employees &middot; grace {{ $shift->grace_minutes }} min &middot;
                                        Weekly off: {{ collect($shift->weekly_offs ?? [])->map(fn ($d) => \App\Models\Hr\Shift::DAYS[$d] ?? $d)->implode(', ') ?: 'none' }}
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#shift-{{ $shift->id }}">Edit</button>
                                    @unless ($shift->is_default)
                                        <form method="POST" action="{{ route('admin.hr.shifts.destroy', $shift) }}" class="d-inline" onsubmit="return confirm('Delete this shift? Its employees move to the default shift.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger p-0 ms-2">Delete</button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.hr.shifts.update', $shift) }}" class="collapse" id="shift-{{ $shift->id }}">
                                @csrf @method('PUT')
                                @include('admin.hr.shifts._fields', ['shift' => $shift])
                                <button class="btn btn-sm btn-outline-secondary mt-2">Save shift</button>
                            </form>
                        </div>
                    @endforeach

                    <h3 class="h6 fw-semibold mt-4">Add a shift</h3>
                    <form method="POST" action="{{ route('admin.hr.shifts.store') }}">
                        @csrf
                        @include('admin.hr.shifts._fields', ['shift' => null])
                        <button class="btn btn-brand btn-sm mt-2">Add shift</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">HR settings</div>
                <form method="POST" action="{{ route('admin.hr.settings.update') }}" class="card-body d-flex flex-column gap-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="form-label small fw-medium">Employee ID prefix</label>
                        <input name="code_prefix" value="{{ old('code_prefix', $codePrefix) }}" class="form-control" required>
                        <div class="form-text">New employees get IDs like <span class="font-monospace">{{ $codePrefix }}0001</span>. Existing IDs do not change.</div>
                    </div>
                    <div>
                        <label class="form-label small fw-medium">Office IP addresses (attendance)</label>
                        <textarea name="allowed_ips" rows="3" class="form-control @error('allowed_ips') is-invalid @enderror" placeholder="Leave empty to allow check-in from anywhere">{{ old('allowed_ips', $allowedIps) }}</textarea>
                        @error('allowed_ips') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Comma-separated. When set, employees can check in / out only from these IPs. Your current IP: <span class="font-monospace">{{ $currentIp }}</span></div>
                    </div>
                    <button class="btn btn-brand btn-sm align-self-start">Save settings</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
