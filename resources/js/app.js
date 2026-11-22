import * as bootstrap from 'bootstrap';

// Exposed globally so data-bs-toggle="modal"/"dropdown"/"tooltip"/etc. attributes
// work anywhere in the app without importing bootstrap in every entry point.
window.bootstrap = bootstrap;

document.documentElement.classList.add('js-ready');

// Dynamically imported so three.js/gsap/motion (and their weight) are only ever
// downloaded on pages that actually have the corresponding element — every other
// page (products, blog, admin, ...) never fetches these chunks at all.
if (document.getElementById('hero-3d-canvas')) {
    import('./three/network-scene').then(({ initNetworkScene }) => initNetworkScene());
}
if (document.querySelector('[data-countup]')) {
    import('./animations/count-up').then(({ initCountUp }) => initCountUp());
}
if (document.querySelector('.team-card')) {
    import('./animations/team-cards').then(({ initTeamCards }) => initTeamCards());
}
if (document.getElementById('msite-nav')) {
    import('./microsite/init').then(({ initMicrosite }) => initMicrosite());
}
if (document.querySelector('[data-apex-for]')) {
    import('./charts/init').then(({ initCharts }) => initCharts());
}
if (document.getElementById('flash-data') || document.querySelector('[data-confirm]')) {
    import('./notify/init').then(({ initNotify }) => initNotify());
}
if (document.querySelector('[data-cart-ajax]')) {
    import('./cart').then(({ initCart }) => initCart());
}
if (document.querySelector('[data-city-picker]')) {
    import('./forms/city-picker').then(({ initCityPicker }) => initCityPicker());
}
if (document.querySelector('[data-permission-matrix]')) {
    import('./forms/permission-matrix').then(({ initPermissionMatrix }) => initPermissionMatrix());
}
if (document.querySelector('[data-mobile-input]')) {
    import('./forms/mobile-input').then(({ initMobileInputs }) => initMobileInputs());
}
if (document.querySelector('[data-slug-preview]') || document.querySelector('[data-price-preview]')) {
    import('./forms/offer-pricing').then(({ initOfferPricing }) => initOfferPricing());
}
if (document.querySelector('[data-member-form]')) {
    import('./forms/member-form').then(({ initMemberForm }) => initMemberForm());
}
if (document.querySelector('[data-page-builder]')) {
    import('./vip/page-builder').then(({ initPageBuilder }) => initPageBuilder());
}
if (document.querySelector('[data-profit-calc]')) {
    import('./forms/profit-calculator').then(({ initProfitCalculator }) => initProfitCalculator());
}

// Generic modal: [data-modal-open="#id"] opens the matching [data-modal]; a
// [data-modal-close] element or a click on the backdrop itself closes it.
function closeModal(modal) {
    if (!modal) return;
    modal.classList.add('d-none');
    modal.classList.remove('d-flex');
    document.body.style.overflow = '';
}

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');
    if (opener) {
        const modal = document.querySelector(opener.getAttribute('data-modal-open'));
        if (modal) {
            // Re-parent to <body> so a transformed ancestor (e.g. a .reveal card)
            // can't become the containing block for this position:fixed overlay.
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.remove('d-none');
            modal.classList.add('d-flex');
            document.body.style.overflow = 'hidden';
        }
        return;
    }

    const closer = event.target.closest('[data-modal-close]');
    if (closer) {
        closeModal(closer.closest('[data-modal]'));
        return;
    }

    // Click on the backdrop container itself (not its children) closes it.
    if (event.target.matches('[data-modal]')) {
        closeModal(event.target);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('[data-modal]:not(.d-none)').forEach(closeModal);
    }
});

// Flash toast: auto-dismisses after a few seconds, or on close-button click.
document.querySelectorAll('[data-flash]').forEach((flash) => {
    const dismiss = () => {
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 300);
    };
    flash.querySelector('[data-flash-close]')?.addEventListener('click', dismiss);
    setTimeout(dismiss, 4000);
});

const revealObserver = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
);

document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));

// Dark-mode toggle: flips <html data-bs-theme> (Bootstrap's own attribute) and
// persists the choice. The no-flash init script in the panel layout applies the
// saved value before first paint; this keeps the sun/moon icon in step with it.
function syncThemeIcons() {
    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    document.querySelectorAll('[data-icon-light]').forEach((el) => el.classList.toggle('d-none', dark));
    document.querySelectorAll('[data-icon-dark]').forEach((el) => el.classList.toggle('d-none', !dark));
}

document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        syncThemeIcons();
        try {
            localStorage.setItem('theme', next);
        } catch (e) {
            /* storage unavailable — theme still applies for this page */
        }
    });
});

syncThemeIcons();

// Admin sidebar mobile toggle
const sidebar = document.getElementById('admin-sidebar');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

function openSidebar() {
    sidebar?.classList.add('is-open');
    sidebarOverlay?.classList.remove('d-none');
}

function closeSidebar() {
    sidebar?.classList.remove('is-open');
    sidebarOverlay?.classList.add('d-none');
}

document.querySelectorAll('[data-sidebar-open]').forEach((btn) => btn.addEventListener('click', openSidebar));
document.querySelectorAll('[data-sidebar-close]').forEach((btn) => btn.addEventListener('click', closeSidebar));
sidebarOverlay?.addEventListener('click', closeSidebar);

// Highlight the sidebar link of the current page. The link whose path is the
// longest match wins, on whole path segments: /admin/hr/employees/5/edit lights
// up "Employees" (/admin/hr/employees), not "HR Dashboard" (/admin/hr).
(function markActiveSidebarLink() {
    if (!sidebar) return;

    const trim = (p) => p.replace(/\/+$/, '') || '/';
    const here = trim(window.location.pathname);
    let best = null;
    let bestLength = -1;

    sidebar.querySelectorAll('nav a[href]').forEach((link) => {
        const url = new URL(link.getAttribute('href'), window.location.origin);
        if (url.origin !== window.location.origin) return;

        const path = trim(url.pathname);
        const matches = here === path || here.startsWith(path === '/' ? '/' : path + '/');

        if (matches && path.length > bestLength) {
            best = link;
            bestLength = path.length;
        }
    });

    if (best) {
        best.classList.add('is-active');
        best.setAttribute('aria-current', 'page');
        // Long menus: scroll the sidebar itself (never the page) so the active
        // link is in view.
        const linkBox = best.getBoundingClientRect();
        const barBox = sidebar.getBoundingClientRect();
        if (linkBox.bottom > barBox.bottom || linkBox.top < barBox.top) {
            sidebar.scrollTop += linkBox.top - barBox.top - sidebar.clientHeight / 2;
        }
    }
})();

// Public header mobile nav toggle
const mobileNav = document.getElementById('mobile-nav');
document.querySelectorAll('[data-mobile-nav-open]').forEach((btn) => {
    btn.addEventListener('click', () => mobileNav?.classList.toggle('d-none'));
});

// Password show/hide toggle: <button data-password-toggle="#password-field-id">
document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const targetSelector = toggle.getAttribute('data-password-toggle');
    const input = document.querySelector(targetSelector);

    if (!input) return;

    toggle.addEventListener('click', () => {
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        toggle.querySelector('[data-icon-eye]')?.classList.toggle('d-none', !showing);
        toggle.querySelector('[data-icon-eye-slash]')?.classList.toggle('d-none', showing);
    });
});

// Auto-add a show/hide eye toggle to EVERY password field that doesn't already
// have one (the manually-wired login form above is detected and left as-is).
(function () {
    const EYE = 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
    const EYE_SLASH = 'M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88';

    const svg = (path, hidden) =>
        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5${hidden ? ' d-none' : ''}" data-eye="${hidden ? 'off' : 'on'}"><path stroke-linecap="round" stroke-linejoin="round" d="${path}"/></svg>`;

    document.querySelectorAll('input[type="password"]').forEach((input) => {
        if (input.dataset.pwEnhanced) return;
        // Leave fields that already have a manually-wired toggle alone.
        if (input.id && document.querySelector(`[data-password-toggle="#${input.id}"]`)) return;
        input.dataset.pwEnhanced = '1';

        const wrap = document.createElement('div');
        wrap.className = 'position-relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.style.paddingRight = '2.5rem';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Show password');
        btn.className = 'btn btn-link text-secondary position-absolute top-0 end-0 h-100 px-3 d-flex align-items-center';
        btn.innerHTML = svg(EYE, false) + svg(EYE_SLASH, true);
        wrap.appendChild(btn);

        btn.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            btn.querySelector('[data-eye="on"]').classList.toggle('d-none', !showing);
            btn.querySelector('[data-eye="off"]').classList.toggle('d-none', showing);
        });
    });
})();

// Chat assistant (lead capture).
// Step 1 is always a button: the visitor picks what they are interested in.
// Only then does the text box open, for name → mobile → email → city, each
// checked before moving on; the lead is then saved through /enquiry. The box
// stays locked (with a hint) whenever typing is not expected, so nothing a
// visitor types is ever silently thrown away.
(function () {
    const root = document.getElementById('chatbot-root');
    if (!root) return;

    const services = JSON.parse(root.dataset.services || '[]');
    const enquiryUrl = root.dataset.enquiryUrl;
    const csrfToken = root.dataset.csrf;

    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const panel = document.getElementById('chatbot-panel');
    const messagesEl = document.getElementById('chatbot-messages');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const sendBtn = form?.querySelector('button[type="submit"]');

    const STEPS = {
        name: { ask: "Great! What's your name?", placeholder: 'Your full name', type: 'text' },
        phone: { ask: 'Thanks! Your 10-digit mobile number?', placeholder: 'e.g. 9876543210', type: 'tel' },
        email: { ask: 'And your email address?', placeholder: 'you@example.com', type: 'email' },
        city: { ask: 'Last one — which city are you in?', placeholder: 'Your city', type: 'text' },
    };
    const ORDER = ['name', 'phone', 'email', 'city'];

    let state;
    let started = false;

    function reset() {
        state = { step: 'menu', service: null, name: null, phone: null, email: null, city: null };
    }

    function scrollDown() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addMessage(text, from) {
        const bubble = document.createElement('div');
        bubble.className = from === 'bot'
            ? 'bg-body border rounded-4 px-3 py-2 text-body'
            : 'bg-brand text-white rounded-4 px-3 py-2 ms-auto';
        bubble.style.maxWidth = '85%';
        bubble.textContent = text;
        messagesEl.appendChild(bubble);
        scrollDown();
    }

    // Lock or open the text box. Locked = disabled with a hint saying why.
    function setInput(open, placeholder, type) {
        if (!input) return;
        input.disabled = !open;
        if (sendBtn) sendBtn.disabled = !open;
        input.type = type || 'text';
        input.placeholder = placeholder || '';
        if (open) input.focus();
    }

    function addButtons(options, onPick) {
        const wrap = document.createElement('div');
        wrap.className = 'd-flex flex-wrap gap-2';
        options.forEach((option) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = option.label;
            btn.className = 'btn btn-sm btn-outline-brand rounded-pill';
            btn.addEventListener('click', () => {
                wrap.remove();
                onPick(option);
            });
            wrap.appendChild(btn);
        });
        messagesEl.appendChild(wrap);
        scrollDown();
    }

    function showMenu() {
        state.step = 'menu';
        addMessage("Hi! I'm the Global Life assistant. What are you interested in? Please pick one:", 'bot');
        addButtons(services, (service) => {
            state.service = service.label;
            addMessage(service.label, 'user');
            ask('name');
        });
        setInput(false, 'Please choose an option above 👆');
    }

    function ask(step) {
        state.step = step;
        addMessage(STEPS[step].ask, 'bot');
        setInput(true, STEPS[step].placeholder, STEPS[step].type);
    }

    // Returns an error message, or null when the answer is fine. Also stores
    // the cleaned value.
    function accept(step, raw) {
        const value = raw.trim();

        if (step === 'name') {
            if (value.length < 2) return 'Please enter your name.';
            state.name = value.slice(0, 150);
        } else if (step === 'phone') {
            const digits = value.replace(/\D/g, '').replace(/^(91|0)(?=\d{10}$)/, '');
            if (!/^[6-9]\d{9}$/.test(digits)) return 'That does not look like a valid 10-digit mobile number. Please try again.';
            state.phone = digits;
        } else if (step === 'email') {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value)) return 'Please enter a valid email, e.g. name@gmail.com.';
            state.email = value.slice(0, 255);
        } else if (step === 'city') {
            if (value.length < 2) return 'Please enter your city.';
            state.city = value.slice(0, 100);
        }

        return null;
    }

    function finish(successText) {
        state.step = 'done';
        addMessage(successText, 'bot');
        setInput(false, 'Thank you!');
        addButtons([{ label: 'Start a new enquiry' }], () => {
            reset();
            showMenu();
        });
    }

    function submitLead() {
        state.step = 'sending';
        setInput(false, 'Sending…');
        addMessage('Thanks! Sending your details now…', 'bot');

        fetch(enquiryUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: state.name,
                phone: state.phone,
                email: state.email,
                city: state.city,
                source: 'chatbot',
                message: `Interested in: ${state.service}`,
            }),
        })
            .then(async (response) => {
                if (response.ok) {
                    finish("You're all set — our team will call you shortly. 🎉");
                    return;
                }

                // Validation error: ask that question again.
                if (response.status === 422) {
                    const body = await response.json().catch(() => ({}));
                    const field = ORDER.find((f) => body.errors && body.errors[f]);
                    if (field) {
                        addMessage(body.errors[field][0], 'bot');
                        ask(field);
                        return;
                    }
                }

                return Promise.reject(response);
            })
            .catch(() => {
                state.step = 'error';
                addMessage('Sorry, that did not go through. Please try again.', 'bot');
                setInput(false, '');
                addButtons([{ label: 'Try again' }], submitLead);
            });
    }

    function startConversation() {
        if (started) return;
        started = true;
        reset();
        showMenu();
    }

    function openChat() {
        panel?.classList.remove('d-none');
        startConversation();
        if (!input?.disabled) input?.focus();
    }

    function closeChat() {
        panel?.classList.add('d-none');
    }

    toggleBtn?.addEventListener('click', () => {
        panel?.classList.contains('d-none') ? openChat() : closeChat();
    });
    closeBtn?.addEventListener('click', closeChat);

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!ORDER.includes(state.step)) return;

        const value = input.value;
        if (!value.trim()) return;

        addMessage(value.trim(), 'user');
        input.value = '';

        const error = accept(state.step, value);
        if (error) {
            addMessage(error, 'bot');
            input.focus();
            return;
        }

        const next = ORDER[ORDER.indexOf(state.step) + 1];
        next ? ask(next) : submitLead();
    });
})();

// Autofocus: mark the first usable field of every form, and put the cursor in
// the very first one on the page. Fields inside a closed <dialog> or a hidden
// container are skipped, so opening a modal later is not fought over, and
// preventScroll stops the page jumping to a form below the fold.
(function () {
    const SKIP_TYPES = ['hidden', 'checkbox', 'radio', 'submit', 'button', 'reset', 'image', 'file'];
    const alreadyMarked = document.querySelector('[autofocus]');
    let focused = Boolean(alreadyMarked);

    document.querySelectorAll('form').forEach((form) => {
        if (form.closest('dialog:not([open])') || form.closest('[hidden]')) return;

        const field = [...form.elements].find((el) => {
            const tag = el.tagName;
            if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') return false;
            if (el.disabled || el.readOnly) return false;
            if (tag === 'INPUT' && SKIP_TYPES.includes(el.type)) return false;
            // offsetParent is null for display:none (and anything inside it).
            return el.offsetParent !== null;
        });

        if (!field) return;

        field.setAttribute('autofocus', '');

        if (!focused) {
            focused = true;
            field.focus({ preventScroll: true });
        }
    });
})();

// Blog post like button (AJAX, no page reload)
const likeButton = document.getElementById('blog-like-button');
likeButton?.addEventListener('click', () => {
    fetch(likeButton.dataset.likeUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
    })
        .then((response) => response.json())
        .then(({ liked, count }) => {
            document.getElementById('blog-like-count').textContent = count;
            likeButton.classList.toggle('btn-danger', liked);
            likeButton.classList.toggle('btn-outline-danger', !liked);
        })
        .catch(() => {});
});

// Copy-link share button
document.querySelectorAll('[data-copy-link]').forEach((btn) => {
    btn.addEventListener('click', () => {
        navigator.clipboard?.writeText(btn.dataset.copyLink).then(() => {
            const original = btn.innerHTML;
            btn.textContent = '✓';
            setTimeout(() => { btn.innerHTML = original; }, 1500);
        });
    });
});

// Global button loading state on form submit — disables the submit button and shows a spinner,
// preventing double-submits and giving feedback while the request is in flight.
document.addEventListener('submit', (event) => {
    const form = event.target;

    // AJAX-handled forms (cart add / quantity / remove) never navigate away, so the
    // spinner would never be restored — leave their in-flight state to their own handler.
    if (!(form instanceof HTMLFormElement) || form.dataset.noLoader || form.hasAttribute('data-cart-ajax')) return;

    const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');

    if (!submitter || submitter.dataset.loading) return;

    submitter.dataset.loading = 'true';
    submitter.dataset.originalContent = submitter.innerHTML;
    submitter.disabled = true;
    submitter.style.minWidth = `${submitter.offsetWidth}px`;
    submitter.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
});
