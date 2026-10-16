<x-layouts.guest title="Reset Password">
    <h1 class="fs-lg fw-semibold text-body mb-6">Reset your password</h1>

    @if ($errors->any())
        <div class="mb-4 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded p-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="d-flex flex-column gap-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="d-block fs-sm fw-medium text-body-secondary">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>

        <div>
            <label for="password" class="d-block fs-sm fw-medium text-body-secondary">New password</label>
            <div class="position-relative mt-1">
                <input id="password" type="password" name="password" required
                       class="d-block w-100 rounded-2 pe-10">
                <button type="button" data-password-toggle="#password"
                        class="btn btn-link p-0 text-decoration-none position-absolute top-0 bottom-0 end-0 px-3 d-flex align-items-center text-secondary">
                    <x-icon name="eye" class="w-5 h-5" data-icon-eye />
                    <x-icon name="eye-slash" class="w-5 h-5 d-none" data-icon-eye-slash />
                </button>
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="d-block fs-sm fw-medium text-body-secondary">Confirm password</label>
            <div class="position-relative mt-1">
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="d-block w-100 rounded-2 pe-10">
                <button type="button" data-password-toggle="#password_confirmation"
                        class="btn btn-link p-0 text-decoration-none position-absolute top-0 bottom-0 end-0 px-3 d-flex align-items-center text-secondary">
                    <x-icon name="eye" class="w-5 h-5" data-icon-eye />
                    <x-icon name="eye-slash" class="w-5 h-5 d-none" data-icon-eye-slash />
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Reset password
        </button>
    </form>
</x-layouts.guest>
