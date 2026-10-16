@csrf
@isset($testimonial) @method('PUT') @endisset

<div class="d-flex flex-column gap-4 mw-xl">
    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="name" class="d-block fs-sm fw-medium text-body-secondary">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $testimonial->name ?? '') }}" required
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="city" class="d-block fs-sm fw-medium text-body-secondary">City</label>
            <input id="city" type="text" name="city" value="{{ old('city', $testimonial->city ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="role_title" class="d-block fs-sm fw-medium text-body-secondary">Role <span class="text-secondary">(Customer, Distributor...)</span></label>
            <input id="role_title" type="text" name="role_title" value="{{ old('role_title', $testimonial->role_title ?? '') }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
        <div>
            <label for="rating" class="d-block fs-sm fw-medium text-body-secondary">Rating</label>
            <select id="rating" name="rating" class="mt-1 d-block w-100 rounded-2">
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected(old('rating', $testimonial->rating ?? 5) == $i)>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                @endfor
            </select>
        </div>
    </div>

    <div>
        <label for="content" class="d-block fs-sm fw-medium text-body-secondary">Testimonial</label>
        <textarea id="content" name="content" rows="4" required
                  class="mt-1 d-block w-100 rounded-2">{{ old('content', $testimonial->content ?? '') }}</textarea>
    </div>

    <div>
        <label for="photo" class="d-block fs-sm fw-medium text-body-secondary">Photo</label>
        @if (isset($testimonial) && $testimonial->photo)
            <img src="{{ asset('storage/'.$testimonial->photo) }}" alt="" class="h-16 w-16 object-fit-cover rounded-pill mt-1 mb-2">
        @endif
        <input id="photo" type="file" name="photo" accept="image/*" class="mt-1 d-block w-100 fs-sm text-body-secondary">
    </div>

    <div class="d-grid grid-cols-1 grid-cols-sm-2 gap-4">
        <div>
            <label for="status" class="d-block fs-sm fw-medium text-body-secondary">Status</label>
            <select id="status" name="status" class="mt-1 d-block w-100 rounded-2">
                @foreach (['active', 'inactive'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $testimonial->status ?? 'active') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="display_order" class="d-block fs-sm fw-medium text-body-secondary">Display Order</label>
            <input id="display_order" type="number" min="0" name="display_order" value="{{ old('display_order', $testimonial->display_order ?? 0) }}"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm">
        {{ isset($testimonial) ? 'Update Testimonial' : 'Create Testimonial' }}
    </button>
</div>
