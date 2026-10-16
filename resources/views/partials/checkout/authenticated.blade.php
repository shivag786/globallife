@php
    $field = 'form-control';
    $defaultId = optional($addresses->firstWhere('is_default', true) ?? $addresses->first())->id;
    $hasAddresses = $addresses->isNotEmpty();
@endphp

{{-- Signed-in bar --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 bg-brand-subtle border border-brand-subtle rounded-3 px-4 py-3 mb-6">
    <p class="fs-sm text-body-secondary">Signed in as <span class="fw-semibold text-brand-emphasis">{{ $user->name }}</span> ({{ $user->email }})</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link p-0 text-decoration-none fs-sm text-secondary">Not you? Sign out</button>
    </form>
</div>

{{-- Add-new-address form (separate form so it can post to the address book) --}}
<div class="bg-body border rounded-4 p-6 mb-6" data-add-address-wrap>
    <div class="d-flex align-items-center justify-content-between">
        <h2 class="fw-semibold text-brand-emphasis">Delivery Addresses</h2>
        @if ($hasAddresses)
            <button type="button" data-add-address-toggle class="btn btn-link p-0 text-decoration-none fs-sm text-brand fw-medium hover-underline">+ Add new address</button>
        @endif
    </div>

    <form method="POST" action="{{ route('account.addresses.store') }}" class="{{ $hasAddresses ? 'd-none' : '' }} mt-5" data-add-address-form>
        @csrf
        <input type="hidden" name="redirect_to" value="checkout">
        @unless ($hasAddresses)
            <p class="fs-sm text-secondary mb-4">Add a delivery address to continue.</p>
        @endunless
        <div class="d-grid grid-cols-sm-2 gap-4">
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">Mobile</label>
                <input type="text" name="phone" value="{{ old('phone', $user->mobile) }}" required class="{{ $field }}">
            </div>
            <div class="col-span-sm-2">
                <label class="d-block fs-sm fw-medium text-body-secondary">Address</label>
                <textarea name="address" rows="2" required class="{{ $field }}">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">City</label>
                <input type="text" name="city" value="{{ old('city') }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">State</label>
                <input type="text" name="state" value="{{ old('state') }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="d-block fs-sm fw-medium text-body-secondary">PIN Code</label>
                <input type="text" name="pincode" value="{{ old('pincode') }}" required class="{{ $field }}">
            </div>
            <label class="d-flex align-items-center gap-2 fs-sm text-body-secondary">
                <input type="checkbox" name="is_default" value="1" class="rounded text-brand"> Make this my default
            </label>
        </div>
        <button type="submit" class="btn btn-brand btn-sm rounded-pill mt-5">Save address</button>
    </form>
</div>

{{-- Main checkout: pick address + payment + place order --}}
<form method="POST" action="{{ route('checkout.store') }}" data-checkout-form class="d-grid grid-template-lg gap-8 align-items-start" style="--cols-lg: minmax(0, 1fr) 340px">
    @csrf
    <div class="d-flex flex-column gap-6">
        @if ($hasAddresses)
            <div class="bg-body border rounded-4 p-6">
                <h2 class="fw-semibold text-brand-emphasis mb-4">Deliver to</h2>
                <div class="d-flex flex-column gap-3">
                    @foreach ($addresses as $address)
                        <label class="d-flex align-items-start gap-3 border rounded-2 p-3">
                            <input type="radio" name="address_id" value="{{ $address->id }}" @checked($defaultId === $address->id) class="mt-1 text-brand">
                            <span class="fs-sm">
                                <span class="d-block fw-medium text-body">
                                    {{ $address->name }} · {{ $address->phone }}
                                    @if ($address->is_default)<span class="ms-1 fs-xs text-brand bg-brand-subtle px-2 py-1 rounded-pill align-middle">Default</span>@endif
                                </span>
                                <span class="d-block text-secondary">{{ $address->summary() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <a href="{{ route('account.addresses.index') }}" class="d-inline-block mt-3 fs-xs text-secondary">Manage addresses →</a>
            </div>

            <div class="bg-body border rounded-4 p-6">
                <label class="d-block fs-sm fw-medium text-body-secondary">Delivery Notes <span class="text-secondary">(optional)</span></label>
                <textarea name="delivery_notes" rows="2" class="{{ $field }}">{{ old('delivery_notes') }}</textarea>
            </div>

            {{-- Payment — the options come from admin > Payment Gateway. --}}
            <div class="bg-body border rounded-4 p-6">
                <h2 class="fw-semibold text-brand-emphasis mb-1">Payment Method</h2>
                <p class="fs-xs text-secondary mb-4">
                    @if ($razorpayEnabled)
                        Payments are processed securely by Razorpay. Your card details never touch our servers.
                    @else
                        Choose how you would like to pay for this order.
                    @endif
                </p>
                <div class="d-flex flex-column gap-3">
                    @foreach ($paymentOptions as $value => $opt)
                        <label class="d-flex align-items-start gap-3 border rounded-2 p-3">
                            <input type="radio" name="payment_choice" value="{{ $value }}"
                                   @checked(old('payment_choice', array_key_first($paymentOptions)) === $value)
                                   class="mt-1 text-brand">
                            <span>
                                <span class="d-block fs-sm fw-medium text-body">{{ $opt['label'] }}</span>
                                <span class="d-block fs-xs text-secondary">{{ $opt['description'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p data-razorpay-error class="d-none mt-3 fs-sm text-danger"></p>
            </div>
        @else
            <div class="bg-body border rounded-4 p-6 fs-sm text-secondary">
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
