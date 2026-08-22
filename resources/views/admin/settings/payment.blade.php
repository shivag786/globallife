<x-layouts.app title="Payment Gateway" heading="Payment Gateway">
    <p class="text-sm text-slate-500 mb-6">
        Controls how customers pay at checkout. Razorpay keys are taken from here — nothing is read from the server environment,
        so keys can be rotated without a deploy.
    </p>

    {{-- .bootstrap-scope activates Bootstrap's real (unprefixed) classes for this
         subtree only — see resources/css/bootstrap.scss / scripts/build-bootstrap.mjs. --}}
    <div class="bootstrap-scope max-w-4xl">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.payment.update') }}">
            @csrf
            @method('PUT')

            <div class="d-flex flex-column gap-4">

                {{-- Razorpay --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h6 mb-0 fw-semibold">Razorpay</h2>
                            <p class="small text-muted mb-0">Cards, UPI, netbanking and wallets.</p>
                        </div>
                        @if ($gateway->razorpayEnabled())
                            <span class="badge bg-success">Live on checkout ({{ $gateway->razorpayMode() }} mode)</span>
                        @elseif ($gateway->razorpayToggledOn())
                            <span class="badge bg-warning text-dark">Enabled but incomplete</span>
                        @else
                            <span class="badge bg-secondary">Disabled</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($gateway->razorpayToggledOn() && ! $gateway->razorpayEnabled())
                            <div class="alert alert-warning small">
                                Razorpay is switched on but the Key ID / Key Secret are missing, so checkout is still falling back to the
                                other methods below. Fill both keys in to go live.
                            </div>
                        @endif

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch" id="razorpay_enabled"
                                   name="razorpay_enabled" value="1"
                                   @checked(old('razorpay_enabled', $gateway->razorpayToggledOn() ? '1' : '0') == '1')>
                            <label class="form-check-label fw-medium" for="razorpay_enabled">Enable Razorpay at checkout</label>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label" for="razorpay_mode">Mode</label>
                                <select class="form-select" id="razorpay_mode" name="razorpay_mode">
                                    <option value="test" @selected(old('razorpay_mode', $gateway->razorpayMode()) === 'test')>Test</option>
                                    <option value="live" @selected(old('razorpay_mode', $gateway->razorpayMode()) === 'live')>Live</option>
                                </select>
                                <div class="form-text">Use the matching key pair from your Razorpay dashboard — test keys start with <code>rzp_test_</code>.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="razorpay_key_id">Key ID</label>
                                <input type="text" class="form-control @error('razorpay_key_id') is-invalid @enderror"
                                       id="razorpay_key_id" name="razorpay_key_id"
                                       value="{{ old('razorpay_key_id', $gateway->razorpayKeyId()) }}"
                                       placeholder="rzp_test_XXXXXXXXXXXX" autocomplete="off">
                                @error('razorpay_key_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="razorpay_key_secret">Key Secret</label>
                                <input type="password" class="form-control @error('razorpay_key_secret') is-invalid @enderror"
                                       id="razorpay_key_secret" name="razorpay_key_secret"
                                       placeholder="{{ $hasSecret ? '•••••••••• (saved)' : 'Enter key secret' }}" autocomplete="new-password">
                                @error('razorpay_key_secret') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">
                                    {{ $hasSecret
                                        ? 'A secret is saved (encrypted). Leave blank to keep it, or type a new one to replace it.'
                                        : 'Stored encrypted and never shown again.' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="razorpay_currency">Currency</label>
                                <input type="text" class="form-control" id="razorpay_currency" name="razorpay_currency"
                                       value="{{ old('razorpay_currency', $gateway->currency()) }}" maxlength="3">
                                <div class="form-text">ISO code, e.g. INR.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Webhook --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h6 mb-0 fw-semibold">Razorpay Webhook</h2>
                            <p class="small text-muted mb-0">Catches payments whose browser never came back.</p>
                        </div>
                        @if ($hasWebhookSecret)
                            <span class="badge bg-success">Configured</span>
                        @else
                            <span class="badge bg-warning text-dark">Not set up</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">
                            If a customer pays and then closes the tab (or loses signal) before returning to the site, the browser never
                            confirms the payment. Razorpay still calls this URL, so the order is created anyway. Without it, that money
                            is taken with no order to show for it.
                        </p>

                        <label class="form-label">Webhook URL</label>
                        <div class="input-group mb-1">
                            <input type="text" class="form-control font-monospace" id="webhook-url" value="{{ $webhookUrl }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" data-copy-webhook>Copy</button>
                        </div>
                        <div class="form-text mb-4">
                            Paste this into Razorpay Dashboard &rarr; Settings &rarr; Webhooks &rarr; Add New Webhook, and subscribe to
                            <code>payment.captured</code> and <code>payment.failed</code>.
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label" for="razorpay_webhook_secret">Webhook Secret</label>
                                <input type="password" class="form-control @error('razorpay_webhook_secret') is-invalid @enderror"
                                       id="razorpay_webhook_secret" name="razorpay_webhook_secret"
                                       placeholder="{{ $hasWebhookSecret ? '•••••••••• (saved)' : 'Paste the secret you set in Razorpay' }}"
                                       autocomplete="new-password">
                                @error('razorpay_webhook_secret') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">
                                    This is the secret you type in while creating the webhook — <strong>not</strong> the API Key Secret above.
                                    {{ $hasWebhookSecret ? 'Leave blank to keep the saved one.' : '' }}
                                </div>
                            </div>
                        </div>

                        @if (! $hasWebhookSecret)
                            <div class="alert alert-warning small mb-0 mt-3">
                                Until this secret is saved, every webhook delivery is rejected as unsigned — deliveries will show as
                                failed in the Razorpay dashboard.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Other methods --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0 fw-semibold">Other Checkout Methods</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="cod_enabled"
                                   name="cod_enabled" value="1"
                                   @checked(old('cod_enabled', $gateway->codEnabled() ? '1' : '0') == '1')>
                            <label class="form-check-label fw-medium" for="cod_enabled">Cash on Delivery</label>
                            <div class="form-text">Payment is collected on delivery and marked paid when the order is delivered.</div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="payment_test_mode"
                                   name="payment_test_mode" value="1"
                                   @checked(old('payment_test_mode', $gateway->testModeEnabled() ? '1' : '0') == '1')>
                            <label class="form-check-label fw-medium" for="payment_test_mode">Simulated test payments</label>
                            <div class="form-text text-danger-emphasis">
                                Adds “Test Payment — Success / Failure” options to checkout. For development only — keep this off on a live store.
                            </div>
                        </div>

                        <p class="small text-muted mb-0 mt-3">
                            If every method is switched off, checkout still falls back to Cash on Delivery so customers are never stuck.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                        Save Payment Settings
                    </button>
                    <span class="text-sm text-slate-400">Changes apply to the next checkout immediately.</span>
                </div>
            </div>
        </form>

        {{-- Credential check (separate form so it doesn't submit the settings above) --}}
        <form method="POST" action="{{ route('admin.settings.payment.test') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm" @disabled(! $gateway->razorpayKeyId() || ! $hasSecret)>
                Test Razorpay connection
            </button>
            <span class="small text-muted ms-2">Calls the Razorpay API with the saved keys. Save first if you just changed them.</span>
        </form>
    </div>

    <script>
        document.querySelector('[data-copy-webhook]')?.addEventListener('click', function () {
            var field = document.getElementById('webhook-url');
            field.select();
            navigator.clipboard?.writeText(field.value).then(
                () => { this.textContent = 'Copied'; setTimeout(() => { this.textContent = 'Copy'; }, 1500); },
                () => { document.execCommand('copy'); },
            );
        });
    </script>
</x-layouts.app>
