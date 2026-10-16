<x-layouts.guest title="Two-Factor Authentication">
    <h1 class="fs-lg fw-semibold text-body mb-2">Two-Factor Authentication</h1>
    <p class="fs-sm text-secondary mb-6">Enter the authentication code from your app, or one of your recovery codes.</p>

    @if ($errors->any())
        <div class="mb-4 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded p-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="d-flex flex-column gap-4">
        @csrf

        <div>
            <label for="code" class="d-block fs-sm fw-medium text-body-secondary">Authentication code</label>
            <input id="code" type="text" name="code" inputmode="numeric" autofocus
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>

        <p class="fs-xs text-secondary text-center">— or, if you've lost access to your device —</p>

        <div>
            <label for="recovery_code" class="d-block fs-sm fw-medium text-body-secondary">Recovery code</label>
            <input id="recovery_code" type="text" name="recovery_code"
                   class="mt-1 d-block w-100 rounded-2 shadow-sm">
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Verify
        </button>
    </form>
</x-layouts.guest>
