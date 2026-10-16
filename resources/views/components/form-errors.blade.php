{{--
    Validation summary for admin forms. Without this a failed save just bounces
    back to the form with no visible reason — e.g. an image over the 2 MB limit
    or a duplicate name looked like "nothing happened".
--}}
@if ($errors->any())
    <div class="alert alert-danger">
        <p class="fw-semibold mb-1">
            {{ $errors->count() === 1 ? "Couldn't save — please fix this:" : "Couldn't save — please fix these ".$errors->count().' items:' }}
        </p>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
