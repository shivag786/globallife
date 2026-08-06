<x-layouts.shop title="Checkout">
    <div class="max-w-5xl mx-auto px-6 py-16">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-8">Checkout</h1>

        @if ($errors->any())
            <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        @if ($user)
            @include('partials.checkout.authenticated')
        @else
            @include('partials.checkout.identify')
        @endif
    </div>
</x-layouts.shop>
