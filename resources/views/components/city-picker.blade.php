@props([
    // state => [['id' => 1, 'name' => 'Jhansi'], ...]
    'citiesByState' => [],
    'states' => [],
    // Already-selected cities: a collection of City models (multi mode only).
    'selected' => null,
    // Single mode posts one city_id (or new_city[name]/[state]) instead of chips.
    'single' => false,
])

@php
    $selected = $selected ?? collect();
    // Re-populate from old() after a failed submit so nothing typed is lost.
    $oldIds = collect(old('cities', $selected->pluck('id')->all()))->map(fn ($id) => (int) $id);
    $oldNew = collect(old('new_cities', []))->filter(fn ($row) => filled($row['name'] ?? null));
    $preselected = \App\Models\City::whereIn('id', $oldIds)->orderBy('name')->get();
@endphp

@if ($single)
    {{-- Single-select: the same state -> city cascade, but one city, posted as
         city_id — or, when "Other" is chosen, as new_city[name]/[state] for the
         server to find-or-create. The "Other" option carries value="" so a
         no-JS submit still posts an empty city_id rather than a bad one. --}}
    <div data-city-picker="single" data-cities='@json($citiesByState)' class="row g-4">
        <div class="col-md-6">
            <label for="city-picker-state" class="form-label small fw-medium text-body-secondary">State</label>
            <select id="city-picker-state" data-city-state class="form-select">
                <option value="">Select a state&hellip;</option>
                @foreach ($states as $state)
                    <option value="{{ $state }}" @selected(old('new_city.state') === $state)>{{ $state }}</option>
                @endforeach
                <option value="__other__" @selected(old('new_city.state') && ! in_array(old('new_city.state'), $states, true))>
                    Other &mdash; type the state name&hellip;
                </option>
            </select>
            <input type="text" data-city-state-new class="form-control mt-2 d-none"
                   placeholder="Type the state name" maxlength="120"
                   value="{{ old('new_city.state') && ! in_array(old('new_city.state'), $states, true) ? old('new_city.state') : '' }}">
            @error('new_city.state')<p class="fs-sm text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="col-md-6">
            <label for="city-picker-city" class="form-label small fw-medium text-body-secondary">City</label>
            <select id="city-picker-city" name="city_id" data-city-select class="form-select">
                <option value="">Select a state first&hellip;</option>
            </select>
            <input type="text" name="new_city[name]" data-city-name-new class="form-control mt-2 d-none"
                   placeholder="Type the city name" maxlength="120" value="{{ old('new_city.name') }}">
            {{-- Mirrors whichever state is active, so a typed city carries one. --}}
            <input type="hidden" name="new_city[state]" data-city-state-value value="{{ old('new_city.state') }}">
            @error('city_id')<p class="fs-sm text-danger mt-1">{{ $message }}</p>@enderror
            @error('new_city.name')<p class="fs-sm text-danger mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
@else
<div data-city-picker data-cities='@json($citiesByState)'>
    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label for="city-picker-state" class="form-label small fw-medium text-body-secondary">State</label>
            <select id="city-picker-state" data-city-state class="form-select">
                <option value="">Select a state&hellip;</option>
                @foreach ($states as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
                <option value="__other__">Other &mdash; type the state name&hellip;</option>
            </select>
            <input type="text" data-city-state-new class="form-control mt-2 d-none"
                   placeholder="Type the state name" maxlength="120">
        </div>

        <div class="col-md-5">
            <label for="city-picker-city" class="form-label small fw-medium text-body-secondary">City</label>
            <select id="city-picker-city" data-city-select class="form-select" disabled>
                <option value="">Select a state first&hellip;</option>
            </select>
            <input type="text" data-city-name-new class="form-control mt-2 d-none"
                   placeholder="Type the city name" maxlength="120">
        </div>

        <div class="col-md-2">
            <button type="button" data-city-add
                    class="btn w-100 text-white"
                    style="background-color:#245a3f;">
                Add
            </button>
        </div>
    </div>

    <p data-city-error class="d-none fs-sm text-danger mt-2"></p>

    <div class="mt-3">
        <p class="small text-muted mb-2">Cities this partner serves</p>
        <div data-city-chips class="d-flex flex-wrap gap-2">
            {{-- Server-rendered chips: what is already saved, or whatever survived
                 a failed submit. The picker appends new ones in the same shape. --}}
            @foreach ($preselected as $city)
                <span data-chip-city-id="{{ $city->id }}"
                      class="d-inline-flex align-items-center gap-2 bg-brand-subtle text-brand-emphasis border border-brand-subtle rounded-pill ps-3 pe-1 py-1 fs-sm">
                    <span>{{ $city->name }}, {{ $city->state }}</span>
                    <input type="hidden" name="cities[]" value="{{ $city->id }}">
                    <button type="button" onclick="this.closest('[data-chip-city-id]').remove()"
                            class="btn btn-link p-0 text-decoration-none w-5 h-5 rounded-pill text-brand lh-1"
                            aria-label="Remove {{ $city->name }}">&times;</button>
                </span>
            @endforeach

            @foreach ($oldNew as $i => $row)
                <span data-chip-key="{{ strtolower(($row['name'] ?? '').'|'.($row['state'] ?? '')) }}"
                      class="d-inline-flex align-items-center gap-2 bg-brand-subtle text-brand-emphasis border border-brand-subtle rounded-pill ps-3 pe-1 py-1 fs-sm">
                    <span>{{ $row['name'] }}, {{ $row['state'] ?? '' }} (new)</span>
                    <input type="hidden" name="new_cities[{{ $i }}][name]" value="{{ $row['name'] }}">
                    <input type="hidden" name="new_cities[{{ $i }}][state]" value="{{ $row['state'] ?? '' }}">
                    <button type="button" onclick="this.closest('[data-chip-key]').remove()"
                            class="btn btn-link p-0 text-decoration-none w-5 h-5 rounded-pill text-brand lh-1"
                            aria-label="Remove {{ $row['name'] }}">&times;</button>
                </span>
            @endforeach
        </div>

        <p data-city-empty class="small text-muted mb-0 {{ $preselected->isEmpty() && $oldNew->isEmpty() ? '' : 'd-none' }}">
            No cities added yet &mdash; pick a state and city above, then press Add.
        </p>
    </div>

    @error('cities')<p class="fs-sm text-danger mt-2">{{ $message }}</p>@enderror
    @error('new_cities')<p class="fs-sm text-danger mt-2">{{ $message }}</p>@enderror
    @foreach ($errors->get('new_cities.*.name') as $messages)
        @foreach ($messages as $message)<p class="fs-sm text-danger mt-2">{{ $message }}</p>@endforeach
    @endforeach
    @foreach ($errors->get('new_cities.*.state') as $messages)
        @foreach ($messages as $message)<p class="fs-sm text-danger mt-2">{{ $message }}</p>@endforeach
    @endforeach
</div>
@endif
