@php $field = 'form-control'; @endphp

<div class="d-grid grid-template-lg gap-8 align-items-start" style="--cols-lg: minmax(0, 1fr) 340px" data-identify>
    <div class="bg-body border rounded-4 p-6">
        {{-- Tabs --}}
        <div class="d-flex gap-2 mb-6 bg-body-secondary p-1 rounded-pill">
            <button type="button" data-tab="register" class="btn btn-link p-0 text-decoration-none flex-grow-1 py-2 rounded-pill fs-sm fw-medium bg-body text-brand-emphasis shadow-sm">New customer</button>
            <button type="button" data-tab="login" class="btn btn-link p-0 text-decoration-none flex-grow-1 py-2 rounded-pill fs-sm fw-medium text-secondary">I have an account</button>
        </div>

        {{-- Register --}}
        <form method="POST" action="{{ route('checkout.register') }}" data-pane="register" data-checkout-auth>
            @csrf
            <p class="fs-sm text-secondary mb-4">Create your account to place the order and track delivery.</p>
            <div data-errors class="d-none mb-4 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded-2 p-3"></div>
            <div class="d-grid grid-cols-sm-2 gap-4">
                <div class="col-span-sm-2">
                    <label class="d-block fs-sm fw-medium text-body-secondary">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="{{ $field }}">
                </div>
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="{{ $field }}">
                </div>
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Mobile</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" required class="{{ $field }}">
                </div>
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Password</label>
                    <input type="password" name="password" required minlength="8" class="{{ $field }}">
                </div>
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" class="{{ $field }}">
                </div>
            </div>
            <button type="submit" class="btn btn-brand rounded-pill mt-5 w-100">Create account &amp; continue</button>
        </form>

        {{-- Login --}}
        <form method="POST" action="{{ route('checkout.login') }}" data-pane="login" data-checkout-auth class="d-none">
            @csrf
            <p class="fs-sm text-secondary mb-4">Welcome back — sign in to continue to delivery &amp; payment.</p>
            <div data-errors class="d-none mb-4 fs-sm text-danger bg-danger-subtle border border-danger-subtle rounded-2 p-3"></div>
            <div class="d-flex flex-column gap-4">
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Email</label>
                    <input type="email" name="email" required class="{{ $field }}">
                </div>
                <div>
                    <label class="d-block fs-sm fw-medium text-body-secondary">Password</label>
                    <input type="password" name="password" required class="{{ $field }}">
                    <a href="{{ route('password.request') }}" class="d-inline-block mt-2 fs-xs text-brand hover-underline">Forgot password?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-brand rounded-pill mt-5 w-100">Sign in &amp; continue</button>
        </form>
    </div>

    @include('partials.checkout.summary', ['placeOrder' => false])
</div>

<script>
(function () {
    var root = document.querySelector('[data-identify]');
    if (!root) return;

    // Tab switching.
    var tabs = root.querySelectorAll('[data-tab]');
    var panes = root.querySelectorAll('[data-pane]');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var name = tab.dataset.tab;
            tabs.forEach(function (t) {
                var on = t.dataset.tab === name;
                t.classList.toggle('bg-body', on);
                t.classList.toggle('text-brand-emphasis', on);
                t.classList.toggle('shadow-sm', on);
                t.classList.toggle('text-secondary', !on);
            });
            panes.forEach(function (p) { p.classList.toggle('d-none', p.dataset.pane !== name); });
        });
    });

    // Progressive AJAX: submit register/login without losing the page; on success
    // reload into the authenticated checkout, on error show messages inline.
    var token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    root.querySelectorAll('form[data-checkout-auth]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var box = form.querySelector('[data-errors]');
            var btn = form.querySelector('button[type="submit"]');
            box.classList.add('d-none');
            btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                body: new FormData(form),
            })
                // A server error / expired session answers with HTML, not JSON —
                // fall back to a readable message instead of throwing, which used
                // to leave the form silently stuck with no feedback.
                .then(function (r) {
                    return r.json()
                        .catch(function () {
                            return {
                                message: r.status === 419
                                    ? 'Your session expired. Please refresh the page and try again.'
                                    : 'Server error (' + r.status + '). Please try again, or contact us if it keeps happening.',
                            };
                        })
                        .then(function (d) { return { ok: r.ok, data: d }; });
                })
                .then(function (res) {
                    if (res.ok) { window.location.reload(); return; }
                    var msgs = res.data && res.data.errors
                        ? Object.values(res.data.errors).flat()
                        : [(res.data && res.data.message) || 'Something went wrong.'];
                    btn.disabled = false;

                    // Already-registered email → move them to the login tab, prefilled.
                    var alreadyHasAccount = form.dataset.pane === 'register'
                        && msgs.some(function (m) { return /already have an account/i.test(m); });
                    if (alreadyHasAccount) {
                        var loginTab = root.querySelector('[data-tab="login"]');
                        var loginForm = root.querySelector('[data-pane="login"]');
                        if (loginTab) loginTab.click();
                        if (loginForm) {
                            loginForm.querySelector('input[name="email"]').value = form.querySelector('input[name="email"]').value;
                            var lb = loginForm.querySelector('[data-errors]');
                            lb.innerHTML = msgs.map(function (m) { return '<div>' + m + '</div>'; }).join('');
                            lb.classList.remove('d-none');
                        }
                        return;
                    }

                    box.innerHTML = msgs.map(function (m) { return '<div>' + m + '</div>'; }).join('');
                    box.classList.remove('d-none');
                })
                // Network-level failure (offline, DNS, connection reset).
                .catch(function () {
                    btn.disabled = false;
                    box.innerHTML = '<div>Could not reach the server. Check your connection and try again.</div>';
                    box.classList.remove('d-none');
                });
        });
    });
})();
</script>
