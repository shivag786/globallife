@csrf
@isset($event) @method('PUT') @endisset

<div class="space-y-4 max-w-2xl">
    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Event Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="event_date" class="block text-sm font-medium text-slate-700">Date &amp; Time</label>
            <input id="event_date" type="datetime-local" name="event_date"
                   value="{{ old('event_date', isset($event) ? $event->event_date->format('Y-m-d\TH:i') : '') }}" required
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label for="location" class="block text-sm font-medium text-slate-700">Location</label>
            <input id="location" type="text" name="location" value="{{ old('location', $event->location ?? '') }}"
                   placeholder="Online / City name"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="5"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $event->description ?? '') }}</textarea>
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-slate-700">Image</label>
        @if (isset($event) && $event->image)
            <img src="{{ asset('storage/'.$event->image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="image" type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-slate-600">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $event->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="block text-sm font-medium text-slate-700">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $event->display_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-md hover:bg-indigo-700">
        {{ isset($event) ? 'Update Event' : 'Create Event' }}
    </button>
</div>
