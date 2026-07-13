import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { hover, animate } from 'motion';

gsap.registerPlugin(ScrollTrigger);

// Stagger the Team Members cards in on scroll (GSAP), then add a Motion-driven
// hover lift + shadow once they've settled.
export function initTeamCards() {
    const cards = document.querySelectorAll('.team-card');
    if (!cards.length) return;

    gsap.from(cards, {
        opacity: 0,
        y: 28,
        duration: 0.6,
        ease: 'power2.out',
        stagger: 0.12,
        scrollTrigger: {
            trigger: cards[0].closest('.grid') || cards[0],
            start: 'top 85%',
            once: true,
        },
    });

    cards.forEach((card) => {
        hover(card, () => {
            const stop = animate(card, { y: -8, boxShadow: '0 20px 40px -20px rgba(15,41,28,0.35)' }, { duration: 0.25 });

            return () => animate(card, { y: 0, boxShadow: '0 0px 0px rgba(15,41,28,0)' }, { duration: 0.25 });
        });
    });
}
