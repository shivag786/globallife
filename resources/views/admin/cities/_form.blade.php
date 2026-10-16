@csrf
@isset($city) @method('PUT') @endisset

<div class="d-flex flex-column gap-4 mw-lg">
    <div>
        <label for="name" class="d-block fs-sm fw-medium text-body-secondary">City Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $city->name ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="state" class="d-block fs-sm fw-medium text-body-secondary">State</label>
        <input id="state" type="text" name="state" value="{{ old('state', $city->state ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div>
        <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
        <select id="status" name="status" class="mt-1 d-block w-100 rounded-2">
            @foreach (['active', 'inactive'] as $option)
                <option value="{{ $option }}" @selected(old('status', $city->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($city) ? 'Update City' : 'Create City' }}
    </button>
</div>
