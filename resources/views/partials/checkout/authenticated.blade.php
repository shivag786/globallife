@php
    $field = 'mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500';
    $defaultId = optional($addresses->firstWhere('is_default', true) ?? $addresses->first())->id;
    $hasAddresses = $addresses->isNotEmpty();
@endphp

{{-- Signed-in bar --}}
<div class="flex flex-wrap items-center justify-between gap-3 bg-brand-50 border border-brand-100 rounded-xl px-4 py-3 mb-6">
    <p class="text-sm text-slate-600">Signed in as <span class="font-semibold text-brand-900">{{ $user->name }}</span> ({{ $user->email }})</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm text-slate-500 hover:text-red-600">Not you? Sign out</button>
    </form>
</div>

{{-- Add-new-address form (separate form so it can post to the address book) --}}
<div class="bg-white border border-slate-100 rounded-2xl p-6 mb-6" data-add-address-wrap>
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-brand-900">Delivery Addresses</h2>
        @if ($hasAddresses)
            <button type="button" data-add-address-toggle class="text-sm text-brand-700 font-medium hover:underline">+ Add new address</button>
        @endif
    </div>

    <form method="POST" action="{{ route('account.addresses.store') }}" class="{{ $hasAddresses ? 'hidden' : '' }} mt-5" data-add-address-form>
        @csrf
        <input type="hidden" name="redirect_to" value="checkout">
        @unless ($hasAddresses)
            <p class="text-sm text-slate-500 mb-4">Add a delivery address to continue.</p>
        @endunless
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Mobile</label>
                <input type="text" name="phone" value="{{ old('phone', $user->mobile) }}" required class="{{ $field }}">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Address</label>
                <textarea name="address" rows="2" required class="{{ $field }}">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">City</label>
                <input type="text" name="city" value="{{ old('city') }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">State</label>
                <input type="text" name="state" value="{{ old('state') }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">PIN Code</label>
                <input type="text" name="pincode" value="{{ old('pincode') }}" required class="{{ $field }}">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_default" value="1" class="rounded text-brand-600 focus:ring-brand-500"> Make this my default
            </label>
        </div>
        <button type="submit" class="mt-5 bg-brand-700 text-white text-sm px-5 py-2.5 rounded-full font-medium hover:bg-brand-800 transition">Save address</button>
    </form>
</div>

{{-- Main checkout: pick address + payment + place order --}}
<form method="POST" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-[1fr_340px] gap-8 items-start">
    @csrf
    <div class="space-y-6">
        @if ($hasAddresses)
            <div class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="font-semibold text-brand-900 mb-4">Deliver to</h2>
                <div class="space-y-2.5">
                    @foreach ($addresses as $address)
                        <label class="flex items-start gap-3 border border-slate-200 rounded-lg p-3.5 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 transition">
                            <input type="radio" name="address_id" value="{{ $address->id }}" @checked($defaultId === $address->id) class="mt-1 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm">
                                <span class="block font-medium text-slate-800">
                                    {{ $address->name }} · {{ $address->phone }}
                                    @if ($address->is_default)<span class="ml-1 text-xs text-brand-700 bg-brand-100 px-1.5 py-0.5 rounded-full align-middle">Default</span>@endif
                                </span>
                                <span class="block text-slate-500">{{ $address->summary() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <a href="{{ route('account.addresses.index') }}" class="inline-block mt-3 text-xs text-slate-500 hover:text-brand-700">Manage addresses →</a>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl p-6">
                <label class="block text-sm font-medium text-slate-700">Delivery Notes <span class="text-slate-400">(optional)</span></label>
                <textarea name="delivery_notes" rows="2" class="{{ $field }}">{{ old('delivery_notes') }}</textarea>
            </div>

            {{-- Payment --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="font-semibold text-brand-900 mb-1">Payment Method</h2>
                <p class="text-xs text-slate-400 mb-4">Using a test gateway — no real charge is made.</p>
                <div class="space-y-2.5">
                    @foreach ([
                        'online_success' => ['Test Payment — Success', 'Simulates a successful online payment.'],
                        'cod' => ['Cash on Delivery', 'Pay in cash when your order arrives.'],
                        'online_fail' => ['Test Payment — Failure', 'Simulates a declined payment (to test the flow).'],
                    ] as $value => $opt)
                        <label class="flex items-start gap-3 border border-slate-200 rounded-lg p-3.5 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 transition">
                            <input type="radio" name="payment_choice" value="{{ $value }}" @checked(old('payment_choice', 'online_success') === $value) class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-medium text-slate-800">{{ $opt[0] }}</span>
                                <span class="block text-xs text-slate-500">{{ $opt[1] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white border border-slate-100 rounded-2xl p-6 text-sm text-slate-500">
                Please add a delivery address above to continue.
            </div>
        @endif
    </div>

    @include('partials.checkout.summary', ['placeOrder' => $hasAddresses])
</form>

<script>
(function () {
    var toggle = document.querySelector('[data-add-address-toggle]');
    var form = document.querySelector('[data-add-address-form]');
    if (toggle && form) {
        toggle.addEventListener('click', function () { form.classList.toggle('hidden'); });
    }
})();
</script>
