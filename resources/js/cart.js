// AJAX cart: add / update quantity / remove without a page reload, with a
// fly-to-cart animation, badge bump, in-place quantity stepper, and a toast.
// Progressive enhancement — every control is a real <form>, so it still works
// (via a normal redirect) if this script never runs.

export function initCart() {
    const token = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const cssEscape = (value) =>
        window.CSS && CSS.escape ? CSS.escape(value) : String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');

    async function post(form) {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token() },
            body: new FormData(form),
        });
        if (!response.ok) throw new Error('cart request failed');
        return response.json();
    }

    function bump(el) {
        try {
            el.animate(
                [{ transform: 'scale(0.5)' }, { transform: 'scale(1.35)' }, { transform: 'scale(1)' }],
                { duration: 420, easing: 'cubic-bezier(.2,.8,.3,1)' },
            );
        } catch (e) { /* Web Animations unsupported — no-op */ }
    }

    function updateBadges(count) {
        document.querySelectorAll('[data-cart-count]').forEach((el) => {
            el.textContent = count > 9 ? '9+' : count;
            el.classList.toggle('hidden', count <= 0);
            if (count > 0) bump(el);
        });
    }

    function updateSummary(totals) {
        if (!totals) return;
        const set = (sel, val) => document.querySelectorAll(sel).forEach((e) => { e.textContent = val; });
        set('[data-summary-subtotal]', totals.subtotal);
        set('[data-summary-shipping]', totals.shipping);
        set('[data-summary-total]', totals.total);
    }

    function flyToCart(source) {
        const target = document.querySelector('[data-cart-icon]');
        if (!target || !source) return;
        const s = source.getBoundingClientRect();
        const t = target.getBoundingClientRect();
        if (!s.width) return;

        const size = Math.min(s.width, 78);
        const dot = document.createElement('div');
        dot.style.cssText = `position:fixed;left:${s.left}px;top:${s.top}px;width:${size}px;height:${size}px;border-radius:16px;z-index:80;pointer-events:none;background:#245a3f;background-size:cover;background-position:center;box-shadow:0 12px 30px rgba(0,0,0,.25);`;
        const img = source.tagName === 'IMG' ? source : source.querySelector('img');
        if (img && img.src) dot.style.backgroundImage = `url('${img.src}')`;
        document.body.appendChild(dot);

        const dx = t.left + t.width / 2 - (s.left + size / 2);
        const dy = t.top + t.height / 2 - (s.top + size / 2);
        const animation = dot.animate(
            [
                { transform: 'translate(0,0) scale(1)', opacity: 1 },
                { transform: `translate(${dx * 0.5}px, ${dy * 0.5 - 40}px) scale(0.6)`, opacity: 0.95, offset: 0.6 },
                { transform: `translate(${dx}px, ${dy}px) scale(0.1)`, opacity: 0.2 },
            ],
            { duration: 720, easing: 'cubic-bezier(.4,.08,.2,1)' },
        );
        animation.onfinish = () => { dot.remove(); bump(target); };
    }

    // Flip a storefront card's controls between the "Add" button and the stepper.
    function applyControls(key, quantity) {
        document.querySelectorAll(`[data-cart-controls][data-key="${cssEscape(key)}"]`).forEach((wrap) => {
            const add = wrap.querySelector('[data-role="add"]');
            const stepper = wrap.querySelector('[data-role="stepper"]');
            const qtyEl = wrap.querySelector('[data-qty]');
            if (quantity > 0) {
                add?.classList.add('hidden');
                stepper?.classList.remove('hidden');
                if (qtyEl) qtyEl.textContent = quantity;
                wrap.querySelectorAll('[data-role="dec"] input[name="quantity"]').forEach((i) => { i.value = quantity - 1; });
                wrap.querySelectorAll('[data-role="inc"] input[name="quantity"]').forEach((i) => { i.value = quantity + 1; });
            } else {
                add?.classList.remove('hidden');
                stepper?.classList.add('hidden');
            }
        });
        document.querySelectorAll(`[data-cart-added="${cssEscape(key)}"]`).forEach((b) => b.classList.toggle('hidden', quantity <= 0));
    }

    function toast(message, isError = false) {
        const el = document.createElement('div');
        el.className = 'fixed top-4 left-1/2 z-[70] px-4 py-2.5 rounded-full text-sm font-medium text-white shadow-lg';
        el.style.cssText += `background:${isError ? '#dc2626' : '#245a3f'};opacity:0;transform:translate(-50%,-8px);transition:opacity .25s,transform .25s;`;
        el.textContent = message;
        document.body.appendChild(el);
        requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translate(-50%,0)'; });
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translate(-50%,-8px)';
            setTimeout(() => el.remove(), 280);
        }, 2200);
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-cart-ajax]');
        if (!form) return;
        event.preventDefault();
        if (form.dataset.busy) return;
        form.dataset.busy = '1';

        const wrap = form.closest('[data-cart-controls]');
        const line = form.closest('[data-cart-line]');
        const isAdd = form.dataset.role === 'add';

        try {
            const data = await post(form);
            updateBadges(data.count);
            updateSummary(data.totals);

            if (isAdd) {
                const media = wrap?.closest('[data-product-card]')?.querySelector('img')
                    || document.querySelector('[data-product-media] img');
                flyToCart(media);
                applyControls(data.key, data.quantity);
                toast(data.message || 'Added to cart');
            } else if (line) {
                if (data.removed || data.quantity === 0) {
                    line.style.transition = 'opacity .3s, transform .3s';
                    line.style.opacity = '0';
                    line.style.transform = 'translateX(-14px)';
                    setTimeout(() => {
                        line.remove();
                        if (data.empty) window.location.reload();
                    }, 300);
                } else {
                    const qtyEl = line.querySelector('[data-qty]');
                    if (qtyEl) qtyEl.textContent = data.quantity;
                    const lt = line.querySelector('[data-line-total]');
                    if (lt && data.line_total) lt.textContent = data.line_total;
                    line.querySelectorAll('[data-role="dec"] input[name="quantity"]').forEach((i) => { i.value = data.quantity - 1; });
                    line.querySelectorAll('[data-role="inc"] input[name="quantity"]').forEach((i) => { i.value = data.quantity + 1; });
                }
            } else if (wrap) {
                applyControls(data.key, data.quantity);
            }
        } catch (e) {
            toast('Something went wrong. Please try again.', true);
        } finally {
            delete form.dataset.busy;
        }
    });
}
