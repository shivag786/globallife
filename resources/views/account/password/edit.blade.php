{{-- Change Password for every role. $layout is layouts.app (dashboard roles,
     which already render flash + errors) or layouts.public (customers). --}}
@php $isPublic = $layout === 'layouts.public'; @endphp

<x-dynamic-component :component="$layout" title="Change Password" heading="Change Password">
    <div @class(['container-lg py-8' => $isPublic])>
        <div class="bg-body border rounded-4 p-4 p-sm-5 mx-auto" style="max-width: 32rem">
            <h2 class="h5 fw-semibold mb-1">Change your password</h2>
            <p class="small text-secondary mb-4">
                Enter your current password, then choose a new one (at least 8 characters).
                Other devices will be signed out and you'll get a confirmation email.
            </p>

            @if ($isPublic)
                @if (session('status'))
                    <div class="alert alert-success small">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger small">
                        <ul class="mb-0 list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif

            <form method="POST" action="{{ route('account.password.update') }}" class="d-flex flex-column gap-3">
                @csrf
                @method('PUT')

                @foreach ([
                    'current_password' => ['Current password', 'current-password'],
                    'password' => ['New password', 'new-password'],
                    'password_confirmation' => ['Confirm new password', 'new-password'],
                ] as $field => [$label, $autocomplete])
                    <div>
                        <label for="{{ $field }}" class="form-label small fw-medium">{{ $label }}</label>
                        <div class="position-relative">
                            <input id="{{ $field }}" type="password" name="{{ $field }}" required
                                   autocomplete="{{ $autocomplete }}" @if ($loop->first) autofocus @endif
                                   class="form-control pe-5 @error($field) is-invalid @enderror">
                            <button type="button" data-password-toggle="#{{ $field }}" aria-label="Show password"
                                    class="btn btn-link p-0 text-decoration-none position-absolute top-0 bottom-0 end-0 px-3 d-flex align-items-center text-secondary">
                                <x-icon name="eye" class="w-5 h-5" data-icon-eye />
                                <x-icon name="eye-slash" class="w-5 h-5 d-none" data-icon-eye-slash />
                            </button>
                        </div>
                        @error($field)
                            <p class="mt-1 mb-0 small text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">Update password</button>
                    <span class="small text-secondary">
                        Forgot it? <a href="{{ route('password.request') }}">Reset by email</a>
                    </span>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
