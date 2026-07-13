import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// Animates each [data-countup] stat from 0 up to its parsed numeric value once it
// scrolls into view, preserving whatever prefix/suffix text surrounds the number
// (e.g. "₹1.2 Cr+", "2.5L+", "99.9%", "19+").
export function initCountUp() {
    const targets = document.querySelectorAll('[data-countup]');
    if (!targets.length) return;

    targets.forEach((el) => {
        const raw = el.dataset.countup || '';
        const match = raw.match(/^([^\d]*)([\d,.]+)(.*)$/);

        if (!match) {
            el.textContent = raw;
            return;
        }

        const [, prefix, numberText, suffix] = match;
        const target = parseFloat(numberText.replace(/,/g, ''));
        const decimals = numberText.includes('.') ? numberText.split('.')[1].length : 0;

        if (Number.isNaN(target)) {
            el.textContent = raw;
            return;
        }

        const counter = { value: 0 };

        gsap.to(counter, {
            value: target,
            duration: 1.6,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: el,
                start: 'top 85%',
                once: true,
            },
            onUpdate() {
                el.textContent = `${prefix}${counter.value.toFixed(decimals)}${suffix}`;
            },
        });
    });
}
