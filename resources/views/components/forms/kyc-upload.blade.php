@props([
    // photo | aadhaar_file | pan_file
    'kind',
    'label',
    // The member being edited (null when creating); used to show the current file.
    'owner' => null,
])

{{--
    Drag-and-drop / tap-to-upload for a KYC image, with a preview.

    The browser shrinks a large phone photo before it is sent (member-form.js),
    so "any size" really works within the 8 MB server limit; the server then
    re-encodes it to WebP, which also strips the photo's location data.

    The passport photo variant previews in a 35:45 frame, the shape it is
    cropped to on save.
--}}
@php
    $isPhoto = $kind === 'photo';
    $hasCurrent = $owner?->profile?->hasFile($kind) ?? false;
    $currentUrl = $hasCurrent ? route('kyc.file', [$owner, $kind]) : null;
    $invalid = $errors->has($kind);
@endphp

<div class="member-upload {{ $isPhoto ? 'member-upload--photo' : '' }}" data-upload
     data-max-dimension="{{ $isPhoto ? 1600 : 2000 }}">
    <label for="{{ $kind }}" class="form-label small fw-medium">
        {{ $label }} <span class="text-secondary fw-normal">(optional)</span>
    </label>

    <div class="member-upload-drop {{ $invalid ? 'is-invalid' : '' }}" data-upload-drop>
        <input type="file" id="{{ $kind }}" name="{{ $kind }}" class="member-upload-input"
               accept=".jpg,.jpeg,.png,.webp,image/*" data-upload-input>

        <div class="member-upload-frame {{ $isPhoto ? 'member-upload-frame--photo' : '' }}">
            <img src="{{ $currentUrl }}" alt="{{ $label }}" data-upload-preview
                 class="{{ $currentUrl ? '' : 'd-none' }}">

            <div class="member-upload-empty {{ $currentUrl ? 'd-none' : '' }}" data-upload-empty>
                <x-icon :name="$isPhoto ? 'user' : 'cloud-arrow-up'" class="w-8 h-8" />
                <span class="fw-semibold fs-sm">{{ $isPhoto ? 'Add a photo' : 'Tap to upload, or drop an image here' }}</span>
                <span class="fs-xs text-secondary">JPG, PNG or WEBP &middot; large photos are shrunk for you</span>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
        <span class="fs-xs text-secondary flex-grow-1" data-upload-status>
            @if ($hasCurrent)
                Current file on record.
                <a href="{{ $currentUrl }}" target="_blank" rel="noopener" class="text-brand hover-underline">Open</a>
            @elseif ($isPhoto)
                Cropped to 35&times;45 mm, the standard ID-card size.
            @endif
        </span>
        @if ($hasCurrent)
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="remove_files[]" value="{{ $kind }}"
                       id="remove-{{ $kind }}" @checked(in_array($kind, (array) old('remove_files', []), true))>
                <label class="form-check-label fs-xs" for="remove-{{ $kind }}">Remove</label>
            </div>
        @endif
    </div>

    @error($kind)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
