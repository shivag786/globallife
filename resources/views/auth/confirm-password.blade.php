<x-layouts.guest title="Confirm Password">
    <h1 class="text-lg font-semibold text-slate-800 mb-2">Confirm your password</h1>
    <p class="text-sm text-slate-500 mb-6">This is a secure area. Please confirm your password before continuing.</p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="relative mt-1">
                <input id="password" type="password" name="password" required autofocus
                       class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10">
                <button type="button" data-password-toggle="#password"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600">
                    <x-icon name="eye" class="w-5 h-5" data-icon-eye />
                    <x-icon name="eye-slash" class="w-5 h-5 hidden" data-icon-eye-slash />
                </button>
            </div>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white rounded-md py-2 font-medium hover:bg-indigo-700">
            Confirm
        </button>
    </form>
</x-layouts.guest>
