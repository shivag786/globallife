<x-layouts.app title="Leave Types" heading="Leave Types">
    @php
        $fields = function ($t = null) {
            return '
            <div class="col-md-3"><label class="form-label small">Name</label><input name="name" value="'.e($t?->name).'" class="form-control form-control-sm" required></div>
            <div class="col-md-1"><label class="form-label small">Code</label><input name="code" value="'.e($t?->code).'" class="form-control form-control-sm" required></div>
            <div class="col-md-2"><label class="form-label small">Days / year</label><input type="number" step="0.5" min="0" name="days_per_year" value="'.e($t?->days_per_year ?? 0).'" class="form-control form-control-sm" required></div>
            <div class="col-md-2"><label class="form-label small">Max carry forward</label><input type="number" step="0.5" min="0" name="max_carry_forward" value="'.e($t?->max_carry_forward ?? 0).'" class="form-control form-control-sm"></div>
            <div class="col-md-4 d-flex flex-wrap gap-3 align-items-end small">
                <label class="form-check mb-1"><input type="checkbox" class="form-check-input" name="is_paid" value="1" '.($t === null || $t->is_paid ? 'checked' : '').'> Paid</label>
                <label class="form-check mb-1"><input type="checkbox" class="form-check-input" name="carry_forward" value="1" '.($t?->carry_forward ? 'checked' : '').'> Carry forward</label>
                '.($t ? '<label class="form-check mb-1"><input type="checkbox" class="form-check-input" name="is_active" value="1" '.($t->is_active ? 'checked' : '').'> Active</label>' : '').'
            </div>';
        };
    @endphp

    <div class="alert alert-light border small">
        Each employee gets the yearly quota of every type. <strong>Days / year = 0</strong> means no quota is tracked (e.g. Unpaid Leave,
        which can always be applied for). Unpaid types reduce salary as Loss of Pay. A changed quota applies to balances opened from now on.
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">Add leave type</div>
        <form method="POST" action="{{ route('admin.hr.leave-types.store') }}" class="card-body row g-2">
            @csrf
            {!! $fields() !!}
            <div class="col-12"><button class="btn btn-brand btn-sm">Add</button></div>
        </form>
    </div>

    @foreach ($types as $t)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $t->name }}</strong> <span class="font-monospace text-secondary">{{ $t->code }}</span>
                        @unless ($t->is_active) <span class="badge text-bg-secondary">Inactive</span> @endunless
                        <div class="fs-xs text-secondary">
                            {{ $t->hasQuota() ? $t->days_per_year.' days / year' : 'No quota' }} &middot; {{ $t->is_paid ? 'Paid' : 'Unpaid' }}
                            @if ($t->carry_forward) &middot; carries forward up to {{ $t->max_carry_forward }} days @endif
                        </div>
                    </div>
                    <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#lt-{{ $t->id }}">Edit</button>
                </div>
                <form method="POST" action="{{ route('admin.hr.leave-types.update', $t) }}" class="collapse row g-2 mt-2" id="lt-{{ $t->id }}">
                    @csrf @method('PUT')
                    {!! $fields($t) !!}
                    <div class="col-12"><button class="btn btn-sm btn-outline-secondary">Save</button></div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts.app>
