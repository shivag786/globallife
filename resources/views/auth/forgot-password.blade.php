<x-layouts.public title="Forgot Password">
    <section class="min-h-[60vh] flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl premium-shadow p-6 sm:p-8">

            <div class="text-center mb-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-brand-50 flex items-center justify-center">
                    <x-icon name="envelope" class="w-6 h-6 text-brand-600" />
                </div>
                <h1 class="text-xl font-semibold text-slate-800 mb-2">Forgot your password?</h1>
                <p class="text-sm text-slate-500">
                    Enter your email and we'll send you a password reset link.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="email" placeholder="you@example.com"
                           class="block w-full rounded-lg shadow-sm text-sm focus:ring-brand-500
                                  {{ $errors->has('email') ? 'border-red-400 focus:border-red-500' : 'border-slate-300 focus:border-brand-500' }}">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-brand-700 text-white rounded-lg py-2.5 font-medium hover:bg-brand-800 transition">
                    Email password reset link
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Remember your password?
                <a href="{{ route('login') }}" class="font-medium text-brand-700 hover:text-brand-800">Back to login</a>
            </p>
        </div>
    </section>
</x-layouts.public>