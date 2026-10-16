<x-layouts.guest title="Confirm Password">
    <h1 class="fs-lg fw-semibold text-body mb-2">Confirm your password</h1>
    <p class="fs-sm text-secondary mb-6">This is a secure area. Please confirm your password before continuing.</p>

    @if ($errors->any())
        <div class="mb-4 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded p-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="d-flex flex-column gap-4">
        @csrf

        <div>
            <label for="password" class="d-block fs-sm fw-medium text-body-secondary">Password</label>
            <div class="position-relative mt-1">
                <input id="password" type="password" name="password" required autofocus
                       class="d-block w-100 rounded-2 pe-10">
                <button type="button" data-password-toggle="#password"
                        class="btn btn-link p-0 text-decoration-none position-absolute top-0 bottom-0 end-0 px-3 d-flex align-items-center text-secondary">
                    <x-icon name="eye" class="w-5 h-5" data-icon-eye />
                    <x-icon name="eye-slash" class="w-5 h-5 d-none" data-icon-eye-slash />
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Confirm
        </button>
    </form>
</x-layouts.guest>
