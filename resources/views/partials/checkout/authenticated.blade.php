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
<form method="POST" action="{{ route('checkout.store') }}" data-checkout-form class="grid lg:grid-cols-[1fr_340px] gap-8 items-start">
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

            {{-- Payment — the options come from admin > Payment Gateway. --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6">
                <h2 class="font-semibold text-brand-900 mb-1">Payment Method</h2>
                <p class="text-xs text-slate-400 mb-4">
                    @if ($razorpayEnabled)
                        Payments are processed securely by Razorpay. Your card details never touch our servers.
                    @else
                        Choose how you would like to pay for this order.
                    @endif
                </p>
                <div class="space-y-2.5">
                    @foreach ($paymentOptions as $value => $opt)
                        <label class="flex items-start gap-3 border border-slate-200 rounded-lg p-3.5 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/50 transition">
                            <input type="radio" name="payment_choice" value="{{ $value }}"
                                   @checked(old('payment_choice', array_key_first($paymentOptions)) === $value)
                                   class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="block text-sm font-medium text-slate-800">{{ $opt['label'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $opt['description'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p data-razorpay-error class="hidden mt-3 text-sm text-red-600"></p>
            </div>
        @else
            <div class="bg-white border border-slate-100 rounded-2xl p-6 text-sm text-slate-500">
                Please add a delivery address above to continue.
            </div>
        @endif
    </div>

    @include('partials.checkout.summary', ['placeOrder' => $hasAddresses])
</form>

@if ($razorpayEnabled)
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    // Razorpay checkout: ask the server for a gateway order, let Razorpay collect
    // the money, then post the signed result back for verification. The order row
    // is only created once the server has verified that signature.
    (function () {
        var form = document.querySelector('[data-checkout-form]');
        if (!form) return;

        var errorBox = document.querySelector('[data-razorpay-error]');
        var button = form.querySelector('button[type="submit"]');
        var buttonLabel = button ? button.textContent : '';
        var token = document.querySelector('meta[name="csrf-token"]');
        var busy = false;

        function showError(message) {
            if (!errorBox) { alert(message); return; }
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }

        function clearError() {
            if (errorBox) errorBox.classList.add('hidden');
        }

        function setBusy(state, label) {
            busy = state;
            if (!button) return;
            button.disabled = state;
            button.textContent = state ? (label || 'Please wait…') : buttonLabel;
        }

        function post(url, payload) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            }).then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Something went wrong. Please try again.');
                    }
                    return data;
                });
            });
        }

        form.addEventListener('submit', function (event) {
            var choice = form.querySelector('input[name="payment_choice"]:checked');
            if (!choice || choice.value !== 'razorpay') return; // COD / test payments post normally

            event.preventDefault();
            if (busy) return;
            clearError();

            if (typeof window.Razorpay === 'undefined') {
                showError('Could not load the payment window. Check your connection or choose another payment method.');
                return;
            }

            var address = form.querySelector('input[name="address_id"]:checked');
            if (!address) {
                showError('Please select a delivery address.');
                return;
            }

            var notes = form.querySelector('textarea[name="delivery_notes"]');
            setBusy(true, 'Opening payment…');

            post(@json(route('checkout.razorpay.create')), {
                address_id: address.value,
                delivery_notes: notes ? notes.value : null,
            }).then(function (data) {
                var checkout = new window.Razorpay({
                    key: data.key,
                    order_id: data.order_id,
                    amount: data.amount,
                    currency: data.currency,
                    name: data.name,
                    description: data.description,
                    prefill: data.prefill,
                    theme: { color: '#0f766e' },
                    modal: {
                        ondismiss: function () {
                            setBusy(false);
                            showError('Payment was cancelled. You can try again or pick another payment method.');
                        },
                    },
                    handler: function (response) {
                        setBusy(true, 'Confirming payment…');
                        post(@json(route('checkout.razorpay.verify')), {
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                        }).then(function (result) {
                            window.location.href = result.redirect;
                        }).catch(function (error) {
                            setBusy(false);
                            showError(error.message);
                        });
                    },
                });

                checkout.on('payment.failed', function (response) {
                    setBusy(false);
                    showError((response.error && response.error.description) || 'The payment failed. Please try again.');
                });

                checkout.open();
            }).catch(function (error) {
                setBusy(false);
                showError(error.message);
            });
        });
    })();
    </script>
@endif

<script>
(function () {
    var toggle = document.querySelector('[data-add-address-toggle]');
    var form = document.querySelector('[data-add-address-form]');
    if (toggle && form) {
        toggle.addEventListener('click', function () { form.classList.toggle('hidden'); });
    }
})();
</script>
