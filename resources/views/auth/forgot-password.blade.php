<x-layouts.public title="Forgot Password">
    <section class="d-flex align-items-center justify-content-center px-4 py-12 py-sm-16">
        <div class="w-100 mw-md bg-body border rounded-4 premium-shadow p-6 p-sm-8">

            <div class="text-center mb-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-pill bg-brand-subtle d-flex align-items-center justify-content-center">
                    <x-icon name="envelope" class="w-6 h-6 text-brand" />
                </div>
                <h1 class="fs-xl fw-semibold text-body mb-2">Forgot your password?</h1>
                <p class="fs-sm text-secondary">
                    Enter your email and we'll send you a password reset link.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 fs-sm text-success bg-success-subtle border border-success-subtle rounded-2 p-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="d-flex flex-column gap-4">
                @csrf

                <div>
                    <label for="email" class="d-block fs-sm fw-medium text-body-secondary mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="email" placeholder="you@example.com"
                           class="d-block w-100 rounded-2 shadow-sm fs-sm {{ $errors->has('email') ? 'border-danger' : '' }}">
                    @error('email')
                        <p class="mt-2 fs-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="btn btn-brand w-100">
                    Email password reset link
                </button>
            </form>

            <p class="mt-6 text-center fs-sm text-secondary">
                Remember your password?
                <a href="{{ route('login') }}" class="fw-medium text-brand">Back to login</a>
            </p>
        </div>
    </section>
</x-layouts.public>