{{--
    Validation summary for admin forms. Without this a failed save just bounces
    back to the form with no visible reason — e.g. an image over the 2 MB limit
    or a duplicate name looked like "nothing happened".
--}}
@if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-sm font-semibold text-red-800">
            {{ $errors->count() === 1 ? "Couldn't save — please fix this:" : "Couldn't save — please fix these ".$errors->count().' items:' }}
        </p>
        <ul class="mt-1.5 list-disc list-inside space-y-0.5 text-sm text-red-700">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
