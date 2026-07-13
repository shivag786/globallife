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

    if (!(form instanceof HTMLFormElement) || form.dataset.noLoader) return;

    const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');

    if (!submitter || submitter.dataset.loading) return;

    submitter.dataset.loading = 'true';
    submitter.dataset.originalContent = submitter.innerHTML;
    submitter.disabled = true;
    submitter.style.minWidth = `${submitter.offsetWidth}px`;
    submitter.innerHTML =
        '<svg class="animate-spin h-4 w-4 mx-auto" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
});
