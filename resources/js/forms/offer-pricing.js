/**
 * Two small helpers for the service form.
 *
 * Both only ever preview: the slug and the discount are generated server-side on
 * save (Str::slug, and BusinessService::discountPercentage()), so nothing here is
 * submitted and a member with JS off loses the preview, not the feature.
 */

/** Mirrors Laravel's Str::slug closely enough for a preview. */
function slugify(value) {
    return value
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function initSlugPreview() {
    const source = document.querySelector('[data-slug-source]');
    const preview = document.querySelector('[data-slug-preview]');

    if (!source || !preview) return;

    const sync = () => {
        preview.value = slugify(source.value);
    };

    source.addEventListener('input', sync);

    // Only fill a blank preview on load: an existing service keeps the slug it was
    // saved with until its name is actually edited.
    if (!preview.value) sync();
}

const rupees = (amount) =>
    '₹' + amount.toLocaleString('en-IN', { maximumFractionDigits: 2 });

function initPricePreview() {
    const scope = document.querySelector('[data-price-preview]');
    if (!scope) return;

    const mrpInput = scope.querySelector('[data-price-mrp]');
    const saleInput = scope.querySelector('[data-price-sale]');
    const output = scope.querySelector('[data-price-output]');

    if (!mrpInput || !saleInput || !output) return;

    const amount = (input) => {
        const value = parseFloat(input.value);

        return Number.isFinite(value) && value > 0 ? value : null;
    };

    const render = () => {
        const mrp = amount(mrpInput);
        const sale = amount(saleInput);

        // MRP alone and sale alone both show a single price. Only a sale that
        // genuinely undercuts the MRP earns a strike-through and a badge.
        const price = sale ?? mrp;

        if (price === null) {
            output.innerHTML = '<span class="text-muted small">Enter a price to preview</span>';

            return;
        }

        const discounted = sale !== null && mrp !== null && mrp > sale;
        const parts = [`<span class="fs-5 fw-bold text-success">${rupees(price)}</span>`];

        if (discounted) {
            const percent = Math.round(((mrp - sale) / mrp) * 100);
            parts.push(`<span class="text-muted text-decoration-line-through">${rupees(mrp)}</span>`);
            parts.push(
                `<span class="badge rounded-pill text-bg-warning">${percent}% OFF</span>`
            );
        }

        output.innerHTML = parts.join('');
    };

    mrpInput.addEventListener('input', render);
    saleInput.addEventListener('input', render);
    render();
}

export function initOfferPricing() {
    initSlugPreview();
    initPricePreview();
}
