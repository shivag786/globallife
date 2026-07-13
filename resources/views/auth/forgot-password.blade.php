<x-layouts.guest title="Forgot Password">
    <h1 class="text-lg font-semibold text-slate-800 mb-2">Forgot your password?</h1>
    <p class="text-sm text-slate-500 mb-6">Enter your email and we'll send you a password reset link.</p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded p-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white rounded-md py-2 font-medium hover:bg-indigo-700">
            Email password reset link
        </button>
    </form>
</x-layouts.guest>
