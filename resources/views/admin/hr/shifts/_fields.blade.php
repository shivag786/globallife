@php $offs = array_map('intval', $shift?->weekly_offs ?? [0]); @endphp
<div class="row g-2">
    <div class="col-md-4">
        <label class="form-label small">Name</label>
        <input name="name" value="{{ $shift?->name }}" class="form-control form-control-sm" required>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small">Start</label>
        <input type="time" name="start_time" value="{{ $shift ? substr((string) $shift->start_time, 0, 5) : '10:00' }}" class="form-control form-control-sm" required>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small">End</label>
        <input type="time" name="end_time" value="{{ $shift ? substr((string) $shift->end_time, 0, 5) : '19:00' }}" class="form-control form-control-sm" required>
    </div>
    <div class="col-4 col-md-2">
        <label class="form-label small">Grace (min)</label>
        <input type="number" name="grace_minutes" value="{{ $shift?->grace_minutes ?? 15 }}" min="0" class="form-control form-control-sm" required>
    </div>
    <div class="col-4 col-md-1">
        <label class="form-label small">Half (min)</label>
        <input type="number" name="half_day_minutes" value="{{ $shift?->half_day_minutes ?? 240 }}" min="0" class="form-control form-control-sm" required>
    </div>
    <div class="col-4 col-md-1">
        <label class="form-label small">Full (min)</label>
        <input type="number" name="full_day_minutes" value="{{ $shift?->full_day_minutes ?? 480 }}" min="1" class="form-control form-control-sm" required>
    </div>
    <div class="col-12 d-flex flex-wrap gap-3 small align-items-center">
        <span class="text-secondary">Weekly off:</span>
        @foreach (\App\Models\Hr\Shift::DAYS as $i => $day)
            <label class="form-check mb-0">
                <input type="checkbox" class="form-check-input" name="weekly_offs[]" value="{{ $i }}" @checked(in_array($i, $offs, true))>
                {{ substr($day, 0, 3) }}
            </label>
        @endforeach
        <label class="form-check mb-0 ms-md-auto">
            <input type="checkbox" class="form-check-input" name="is_default" value="1" @checked($shift?->is_default)> Default shift
        </label>
    </div>
</div>
