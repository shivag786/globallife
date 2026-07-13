<x-layouts.guest title="Two-Factor Authentication">
    <h1 class="text-lg font-semibold text-slate-800 mb-2">Two-Factor Authentication</h1>
    <p class="text-sm text-slate-500 mb-6">Enter the authentication code from your app, or one of your recovery codes.</p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="code" class="block text-sm font-medium text-slate-700">Authentication code</label>
            <input id="code" type="text" name="code" inputmode="numeric" autofocus
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <p class="text-xs text-slate-400 text-center">— or, if you've lost access to your device —</p>

        <div>
            <label for="recovery_code" class="block text-sm font-medium text-slate-700">Recovery code</label>
            <input id="recovery_code" type="text" name="recovery_code"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white rounded-md py-2 font-medium hover:bg-indigo-700">
            Verify
        </button>
    </form>
</x-layouts.guest>
