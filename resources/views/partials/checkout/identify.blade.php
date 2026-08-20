@php $field = 'mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500'; @endphp

<div class="grid lg:grid-cols-[1fr_340px] gap-8 items-start" data-identify>
    <div class="bg-white border border-slate-100 rounded-2xl p-6">
        {{-- Tabs --}}
        <div class="flex gap-2 mb-6 bg-slate-100 p-1 rounded-full">
            <button type="button" data-tab="register" class="flex-1 py-2 rounded-full text-sm font-medium transition bg-white text-brand-800 shadow-sm">New customer</button>
            <button type="button" data-tab="login" class="flex-1 py-2 rounded-full text-sm font-medium transition text-slate-500">I have an account</button>
        </div>

        {{-- Register --}}
        <form method="POST" action="{{ route('checkout.register') }}" data-pane="register" data-checkout-auth>
            @csrf
            <p class="text-sm text-slate-500 mb-4">Create your account to place the order and track delivery.</p>
            <div data-errors class="hidden mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Mobile</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" required class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required minlength="8" class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" class="{{ $field }}">
                </div>
            </div>
            <button type="submit" class="mt-5 w-full bg-brand-700 text-white py-3 rounded-full font-medium hover:bg-brand-800 transition">Create account &amp; continue</button>
        </form>

        {{-- Login --}}
        <form method="POST" action="{{ route('checkout.login') }}" data-pane="login" data-checkout-auth class="hidden">
            @csrf
            <p class="text-sm text-slate-500 mb-4">Welcome back — sign in to continue to delivery &amp; payment.</p>
            <div data-errors class="hidden mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" required class="{{ $field }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required class="{{ $field }}">
                    <a href="{{ route('password.request') }}" class="inline-block mt-1.5 text-xs text-brand-700 hover:underline">Forgot password?</a>
                </div>
            </div>
            <button type="submit" class="mt-5 w-full bg-brand-700 text-white py-3 rounded-full font-medium hover:bg-brand-800 transition">Sign in &amp; continue</button>
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
                t.classList.toggle('bg-white', on);
                t.classList.toggle('text-brand-800', on);
                t.classList.toggle('shadow-sm', on);
                t.classList.toggle('text-slate-500', !on);
            });
            panes.forEach(function (p) { p.classList.toggle('hidden', p.dataset.pane !== name); });
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
            box.classList.add('hidden');
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
                            lb.classList.remove('hidden');
                        }
                        return;
                    }

                    box.innerHTML = msgs.map(function (m) { return '<div>' + m + '</div>'; }).join('');
                    box.classList.remove('hidden');
                })
                // Network-level failure (offline, DNS, connection reset).
                .catch(function () {
                    btn.disabled = false;
                    box.innerHTML = '<div>Could not reach the server. Check your connection and try again.</div>';
                    box.classList.remove('hidden');
                });
        });
    });
})();
</script>
