<x-layouts.shop title="Checkout">
    <div class="mw-5xl mx-auto px-6 py-16">
        <h1 class="font-display fs-3xl fw-bold text-brand-emphasis mb-8">Checkout</h1>

        @if ($errors->any())
            <div class="mb-6 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded-2 p-4">
                <ul class="list-disc list-inside d-flex flex-column gap-1">
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
