/**
 * Keeps a mobile field at 10 digits, mirroring App\Support\MobileNumber.
 *
 * Bulk input — a paste, or a browser autofilling a saved contact — is reduced to
 * its LAST 10 digits, so "+91 98765 43210" and "09876543210" both become
 * "9876543210" without needing to know which prefixes exist.
 *
 * Typing is treated differently on purpose. Taking the last 10 while someone
 * types would silently drop the digits they entered first: type an 11th and the
 * leading 9 would vanish. So character-by-character entry simply stops at 10,
 * which is what the field's maxlength already implies.
 */
export function initMobileInputs() {
    document.querySelectorAll('[data-mobile-input]').forEach(setup);
}

const LENGTH = 10;

const digitsOf = (value) => (value || '').replace(/\D+/g, '');

/** Last 10 digits — for input that arrived all at once. */
const lastTen = (value) => {
    const digits = digitsOf(value);
    return digits.length > LENGTH ? digits.slice(-LENGTH) : digits;
};

/** First 10 digits — for input being typed one character at a time. */
const firstTen = (value) => digitsOf(value).slice(0, LENGTH);

function setup(input) {
    let previousLength = digitsOf(input.value).length;

    const apply = (next) => {
        if (input.value !== next) {
            input.value = next;
        }
        previousLength = next.length;
    };

    // Whatever was already on the field (an edit form, or a value the browser
    // restored on back-navigation) gets the bulk treatment.
    apply(lastTen(input.value));

    input.addEventListener('input', () => {
        const digits = digitsOf(input.value);
        // More than one new digit at once means it was not typed.
        const bulk = digits.length - previousLength > 1;
        apply(bulk ? lastTen(input.value) : firstTen(input.value));
    });

    // Autofill often fires only `change`, and blur is the last chance to tidy up.
    ['change', 'blur'].forEach((event) => {
        input.addEventListener(event, () => apply(lastTen(input.value)));
    });
}
