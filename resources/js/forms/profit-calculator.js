/**
 * Profit calculator on the VIP member's "Sell Products" page.
 *
 * Figures come from the server, which reads them off the same commission rules
 * that settle a real order — this only multiplies by the unit count. Nothing is
 * submitted anywhere.
 */

const rupees = (amount) =>
    '₹' + Number(amount).toLocaleString('en-IN', { maximumFractionDigits: 2 });

export function initProfitCalculator() {
    const root = document.querySelector('[data-profit-calc]');
    if (!root) return;

    let products = [];
    try {
        products = JSON.parse(root.dataset.products || '[]');
    } catch (e) {
        return;
    }

    if (!products.length) return;

    const productSelect = root.querySelector('[data-calc-product]');
    const unitsInput = root.querySelector('[data-calc-units]');
    const priceOut = root.querySelector('[data-calc-price]');
    const rateOut = root.querySelector('[data-calc-rate]');
    const eachOut = root.querySelector('[data-calc-each]');
    const totalOut = root.querySelector('[data-calc-total]');

    if (!productSelect || !unitsInput) return;

    const render = () => {
        const product = products[Number(productSelect.value)] || products[0];
        if (!product) return;

        // A blank or nonsense unit count reads as one sale rather than blanking
        // the whole panel out while they retype.
        const typed = parseInt(unitsInput.value, 10);
        const units = Number.isFinite(typed) && typed > 0 ? typed : 1;

        priceOut.textContent = rupees(product.price);

        // A flat-amount rule has no percentage to quote, so show what it pays
        // against this price instead of inventing a rate.
        rateOut.textContent =
            product.percent !== null && product.percent !== undefined
                ? `${String(product.percent).replace(/\.00$/, '')}%`
                : rupees(product.earn) + ' flat';

        eachOut.textContent = rupees(product.earn);
        totalOut.textContent = rupees(product.earn * units);
    };

    productSelect.addEventListener('change', render);
    unitsInput.addEventListener('input', render);
    render();
}
