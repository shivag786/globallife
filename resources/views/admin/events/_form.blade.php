@csrf
@isset($event) @method('PUT') @endisset

<div class="d-flex flex-column gap-4 mw-2xl">
    <div>
        <label for="title" class="d-block fs-sm fw-medium text-body-secondary">Event Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required
               class="mt-1 d-block w-100 rounded-2 shadow-sm">
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="event_date" class="d-block fs-sm fw-medium text-body-secondary">Date &amp; Time</label>
            <input id="event_date" type="datetime-local" name="event_date"
                   value="{{ old('event_date', isset($event) ? $event->event_date->format('Y-m-d\TH:i') : '') }}" required
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="location" class="d-block fs-sm fw-medium text-body-secondary">Location</label>
            <input id="location" type="text" name="location" value="{{ old('location', $event->location ?? '') }}"
                   placeholder="Online / City name"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <div>
        <label for="description" class="d-block fs-sm fw-medium text-body-secondary">Description</label>
        <textarea id="description" name="description" rows="5"
                  class="mt-1 d-block w-100 rounded-2 shadow-sm">{{ old('description', $event->description ?? '') }}</textarea>
    </div>

    <div>
        <label for="image" class="d-block fs-sm fw-medium text-body-secondary">Image</label>
        @if (isset($event) && $event->image)
            <img src="{{ asset('storage/'.$event->image) }}" alt="" class="h-16 mt-1 mb-2 rounded">
        @endif
        <input id="image" type="file" name="image" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
            <select id="status" name="status" class="mt-1 d-block w-100 rounded-2 shadow-sm">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $event->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="d-block fs-sm fw-medium text-body-secondary">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $event->display_order ?? 0) }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($event) ? 'Update Event' : 'Create Event' }}
    </button>
</div>
