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

// Generic modal: [data-modal-open="#id"] opens the matching [data-modal]; a
// [data-modal-close] element or a click on the backdrop itself closes it.
function closeModal(modal) {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
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
            modal.classList.remove('hidden');
            modal.classList.add('flex');
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
        document.querySelectorAll('[data-modal]:not(.hidden)').forEach(closeModal);
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

// Dark-mode toggle: flips <html data-theme> and persists the choice. The no-flash
// init script in the panel layout applies the saved value before first paint.
document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try {
            localStorage.setItem('theme', next);
        } catch (e) {
            /* storage unavailable — theme still applies for this page */
        }
    });
});

// Admin sidebar mobile toggle
const sidebar = document.getElementById('admin-sidebar');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

function openSidebar() {
    sidebar?.classList.remove('-translate-x-full');
    sidebarOverlay?.classList.remove('hidden');
}

function closeSidebar() {
    sidebar?.classList.add('-translate-x-full');
    sidebarOverlay?.classList.add('hidden');
}

document.querySelectorAll('[data-sidebar-open]').forEach((btn) => btn.addEventListener('click', openSidebar));
document.querySelectorAll('[data-sidebar-close]').forEach((btn) => btn.addEventListener('click', closeSidebar));
sidebarOverlay?.addEventListener('click', closeSidebar);

// Public header mobile nav toggle
const mobileNav = document.getElementById('mobile-nav');
document.querySelectorAll('[data-mobile-nav-open]').forEach((btn) => {
    btn.addEventListener('click', () => mobileNav?.classList.toggle('hidden'));
});

// Password show/hide toggle: <button data-password-toggle="#password-field-id">
document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const targetSelector = toggle.getAttribute('data-password-toggle');
    const input = document.querySelector(targetSelector);

    if (!input) return;

    toggle.addEventListener('click', () => {
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        toggle.querySelector('[data-icon-eye]')?.classList.toggle('hidden', !showing);
        toggle.querySelector('[data-icon-eye-slash]')?.classList.toggle('hidden', showing);
    });
});

// Auto-add a show/hide eye toggle to EVERY password field that doesn't already
// have one (the manually-wired login form above is detected and left as-is).
(function () {
    const EYE = 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
    const EYE_SLASH = 'M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88';

    const svg = (path, hidden) =>
        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5${hidden ? ' hidden' : ''}" data-eye="${hidden ? 'off' : 'on'}"><path stroke-linecap="round" stroke-linejoin="round" d="${path}"/></svg>`;

    document.querySelectorAll('input[type="password"]').forEach((input) => {
        if (input.dataset.pwEnhanced) return;
        // Leave fields that already have a manually-wired toggle alone.
        if (input.id && document.querySelector(`[data-password-toggle="#${input.id}"]`)) return;
        input.dataset.pwEnhanced = '1';

        const wrap = document.createElement('div');
        wrap.className = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.classList.add('pr-10');

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Show password');
        btn.className = 'absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600';
        btn.innerHTML = svg(EYE, false) + svg(EYE_SLASH, true);
        wrap.appendChild(btn);

        btn.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            btn.querySelector('[data-eye="on"]').classList.toggle('hidden', !showing);
            btn.querySelector('[data-eye="off"]').classList.toggle('hidden', showing);
        });
    });
})();

// Chatbot: a small scripted (non-AI) lead-capture flow.
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

    let state = { step: 'menu', service: null, city: null, name: null, email: null };
    let started = false;

    function addMessage(text, from) {
        const bubble = document.createElement('div');
        bubble.className = from === 'bot'
            ? 'bg-white border border-slate-100 rounded-2xl rounded-bl-sm px-4 py-2 max-w-[85%] text-slate-700'
            : 'bg-brand-700 text-white rounded-2xl rounded-br-sm px-4 py-2 max-w-[85%] ml-auto';
        bubble.textContent = text;
        messagesEl.appendChild(bubble);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addServiceButtons() {
        const wrap = document.createElement('div');
        wrap.className = 'flex flex-wrap gap-2';
        services.forEach((service) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = service.label;
            btn.className = 'text-xs bg-brand-50 text-brand-700 border border-brand-100 px-3 py-1.5 rounded-full hover:bg-brand-100';
            btn.addEventListener('click', () => {
                wrap.remove();
                handleServiceChoice(service.label);
            });
            wrap.appendChild(btn);
        });
        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function handleServiceChoice(label) {
        state.service = label;
        addMessage(label, 'user');
        state.step = 'city';
        addMessage('Great choice! Which city are you in?', 'bot');
    }

    function startConversation() {
        if (started) return;
        started = true;
        addMessage("Hi! I'm the Global Life assistant. What are you interested in?", 'bot');
        addServiceButtons();
    }

    function openChat() {
        panel?.classList.remove('hidden');
        startConversation();
        input?.focus();
    }

    function closeChat() {
        panel?.classList.add('hidden');
    }

    toggleBtn?.addEventListener('click', () => {
        panel?.classList.contains('hidden') ? openChat() : closeChat();
    });
    closeBtn?.addEventListener('click', closeChat);

    function submitLead() {
        addMessage("Thanks! Sending that over now...", 'bot');

        fetch(enquiryUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                name: state.name,
                email: state.email,
                city: state.city,
                source: 'chatbot',
                message: `Interested in: ${state.service}`,
            }),
        })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then(() => {
                addMessage("You're all set — a team member will reach out shortly. 🎉", 'bot');
                state.step = 'done';
            })
            .catch(() => {
                addMessage('Something went wrong sending that. Please try the Contact page instead.', 'bot');
                state.step = 'done';
            });
    }

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        const value = input.value.trim();
        if (!value || state.step === 'done' || state.step === 'menu') {
            input.value = '';
            return;
        }

        addMessage(value, 'user');
        input.value = '';

        if (state.step === 'city') {
            state.city = value;
            state.step = 'name';
            addMessage("And what's your name?", 'bot');
        } else if (state.step === 'name') {
            state.name = value;
            state.step = 'email';
            addMessage('Last thing — your email address?', 'bot');
        } else if (state.step === 'email') {
            state.email = value;
            submitLead();
        }
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
            likeButton.classList.toggle('bg-rose-50', liked);
            likeButton.classList.toggle('border-rose-200', liked);
            likeButton.classList.toggle('text-rose-600', liked);
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
    submitter.innerHTML =
        '<svg class="animate-spin h-4 w-4 mx-auto" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
});
