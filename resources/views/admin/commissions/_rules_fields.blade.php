{{-- Shared commission role rows. Expects: $roles (role => label), $current (role => CommissionRule|null). --}}
<div class="space-y-3">
    <div class="hidden sm:grid grid-cols-[1fr_130px_1fr] gap-3 text-xs uppercase tracking-wide text-slate-400 px-1">
        <span>Role</span>
        <span>Type</span>
        <span>Value</span>
    </div>
    @foreach ($roles as $role => $label)
        @php $rule = $current[$role] ?? null; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_130px_1fr] gap-3 sm:items-center">
            <label for="value-{{ $role }}" class="text-sm font-medium text-slate-700">{{ $label }}</label>
            <select name="rules[{{ $role }}][type]" aria-label="{{ $label }} commission type"
                    class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="percent" @selected(old("rules.$role.type", $rule->type ?? 'percent') === 'percent')>Percent %</option>
                <option value="fixed" @selected(old("rules.$role.type", $rule->type ?? '') === 'fixed')>Fixed ₹</option>
            </select>
            <input id="value-{{ $role }}" type="number" step="0.01" min="0"
                   name="rules[{{ $role }}][value]" value="{{ old("rules.$role.value", $rule && $rule->value !== null ? rtrim(rtrim($rule->value, '0'), '.') : '') }}"
                   placeholder="—"
                   class="block w-full rounded-md border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error("rules.$role.value")
                <p class="sm:col-span-3 text-xs text-red-600 -mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
    <p class="text-xs text-slate-400 pt-1">
        Leave a value blank to remove that role's rule — it will then fall back to the
        category or global default. The company automatically receives whatever remains.
    </p>
</div>
