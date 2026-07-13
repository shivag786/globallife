@csrf
@isset($city) @method('PUT') @endisset

<div class="space-y-4 max-w-lg">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">City Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $city->name ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="state" class="block text-sm font-medium text-slate-700">State</label>
        <input id="state" type="text" name="state" value="{{ old('state', $city->state ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['active', 'inactive'] as $option)
                <option value="{{ $option }}" @selected(old('status', $city->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($city) ? 'Update City' : 'Create City' }}
    </button>
</div>
