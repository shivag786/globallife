{{-- Shared commission role rows. Expects: $roles (role => label), $current (role => CommissionRule|null). --}}
<div class="d-flex flex-column gap-3">
    <div class="d-none d-sm-grid grid-template gap-3 fs-xs text-uppercase tracking-wide text-secondary px-1" style="--cols: minmax(0, 1fr) 130px minmax(0, 1fr)">
        <span>Role</span>
        <span>Type</span>
        <span>Value</span>
    </div>
    @foreach ($roles as $role => $label)
        @php $rule = $current[$role] ?? null; @endphp
        <div class="d-grid grid-template-sm gap-3 align-items-sm-center" style="--cols-sm: minmax(0, 1fr) 130px minmax(0, 1fr)">
            <label for="value-{{ $role }}" class="fs-sm fw-medium text-body-secondary">{{ $label }}</label>
            <select name="rules[{{ $role }}][type]" aria-label="{{ $label }} commission type"
                    class="d-block w-100 rounded-2 shadow-sm fs-sm">
                <option value="percent" @selected(old("rules.$role.type", $rule->type ?? 'percent') === 'percent')>Percent %</option>
                <option value="fixed" @selected(old("rules.$role.type", $rule->type ?? '') === 'fixed')>Fixed ₹</option>
            </select>
            <input id="value-{{ $role }}" type="number" step="0.01" min="0"
                   name="rules[{{ $role }}][value]" value="{{ old("rules.$role.value", $rule && $rule->value !== null ? rtrim(rtrim($rule->value, '0'), '.') : '') }}"
                   placeholder="—"
                   class="d-block w-100 rounded-2 shadow-sm fs-sm">
            @error("rules.$role.value")
                <p class="col-span-sm-3 fs-xs text-danger -n">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
    <p class="fs-xs text-secondary pt-1">
        Leave a value blank to remove that role's rule — it will then fall back to the
        category or global default. The company automatically receives whatever remains.
    </p>
</div>
