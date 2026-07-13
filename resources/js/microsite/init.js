// All interactive behavior for the public VIP business microsite page.
// Pure CSS-transform transitions driven by small vanilla JS controllers — no
// animation library needed here. Dynamically imported by app.js only when
// #msite-nav is present, so this never loads on any other page.

function initStickyNav() {
    const header = document.getElementById('msite-nav');
    const menuOpenBtn = document.querySelector('[data-msite-menu-open]');
    const mobileNav = document.getElementById('msite-mobile-nav');
    if (!header) return;

    window.addEventListener('scroll', () => {
        header.classList.toggle('shadow-md', window.scrollY > 12);
    });

    menuOpenBtn?.addEventListener('click', () => {
        mobileNav?.classList.toggle('hidden');
    });

    document.querySelectorAll('[data-msite-nav-link]').forEach((link) => {
        link.addEventListener('click', () => mobileNav?.classList.add('hidden'));
    });

    const navLinks = document.querySelectorAll('[data-msite-nav-link]');
    if (!navLinks.length) return;

    const sections = Array.from(navLinks)
        .map((link) => document.getElementById(link.dataset.msiteNavLink))
        .filter(Boolean);

    const sectionObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                navLinks.forEach((link) => {
                    link.classList.toggle('is-active', link.dataset.msiteNavLink === entry.target.id);
                });
            });
        },
        { rootMargin: '-40% 0px -55% 0px' },
    );

    sections.forEach((section) => sectionObserver.observe(section));
}

function initHeroSlider() {
    document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
        const track = slider.querySelector('[data-hero-track]');
        const slides = track ? Array.from(track.children) : [];
        if (slides.length <= 1) return;

        let current = 0;
        const dots = slider.querySelectorAll('[data-hero-dot]');

        function goTo(index) {
            current = (index + slides.length) % slides.length;
            track.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((dot, i) => dot.classList.toggle('bg-white/40', i !== current));
            dots.forEach((dot, i) => dot.classList.toggle('bg-white', i === current));
        }

        slider.querySelector('[data-hero-next]')?.addEventListener('click', () => goTo(current + 1));
        slider.querySelector('[data-hero-prev]')?.addEventListener('click', () => goTo(current - 1));
        dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

        let timer = setInterval(() => goTo(current + 1), 6000);
        slider.addEventListener('mouseenter', () => clearInterval(timer));
        slider.addEventListener('mouseleave', () => { timer = setInterval(() => goTo(current + 1), 6000); });

        goTo(0);
    });
}

function initReviewsCarousel() {
    document.querySelectorAll('[data-msite-review-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-msite-review-track]');
        const slides = track ? Array.from(track.children) : [];
        if (slides.length <= 1) return;

        let current = 0;
        const dots = carousel.querySelectorAll('[data-msite-review-dot]');

        function goTo(index) {
            current = (index + slides.length) % slides.length;
            track.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((dot, i) => dot.classList.toggle('bg-brand-500', i === current));
        }

        dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));
        setInterval(() => goTo(current + 1), 7000);
        goTo(0);
    });
}

function initGalleryLightbox() {
    const lightbox = document.getElementById('msite-lightbox');
    const dataEl = document.getElementById('msite-gallery-data');
    if (!lightbox || !dataEl) return;

    const images = JSON.parse(dataEl.textContent || '[]');
    const imgEl = lightbox.querySelector('[data-msite-lightbox-img]');
    let current = 0;

    function show(index) {
        current = (index + images.length) % images.length;
        imgEl.src = images[current];
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
    }

    function close() {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
    }

    document.querySelectorAll('[data-msite-gallery-item]').forEach((btn) => {
        btn.addEventListener('click', () => show(Number(btn.dataset.msiteGalleryItem)));
    });

    lightbox.querySelector('[data-msite-lightbox-close]')?.addEventListener('click', close);
    lightbox.querySelector('[data-msite-lightbox-next]')?.addEventListener('click', () => show(current + 1));
    lightbox.querySelector('[data-msite-lightbox-prev]')?.addEventListener('click', () => show(current - 1));
    lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
}

function initVideoModal() {
    const modal = document.getElementById('msite-video-modal');
    if (!modal) return;

    const frame = modal.querySelector('[data-msite-video-frame]');

    function close() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        frame.src = '';
    }

    document.querySelectorAll('[data-msite-video]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const youtubeId = btn.dataset.msiteVideo;
            if (!youtubeId) return;
            frame.src = `https://www.youtube.com/embed/${youtubeId}?autoplay=1`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    modal.querySelector('[data-msite-video-close]')?.addEventListener('click', close);
    modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
}

function initFloatingScrollBehavior() {
    const floatGroup = document.querySelector('[data-msite-float-group]');
    const backToTop = document.getElementById('msite-back-to-top');
    if (!floatGroup && !backToTop) return;

    let lastScrollY = window.scrollY;

    window.addEventListener('scroll', () => {
        const scrollingDown = window.scrollY > lastScrollY;
        const pastFold = window.scrollY > 400;

        floatGroup?.querySelectorAll('.msite-float-btn').forEach((btn) => {
            btn.classList.toggle('is-hidden', scrollingDown && pastFold);
        });

        backToTop?.classList.toggle('is-hidden', !pastFold);

        lastScrollY = window.scrollY;
    });

    backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

export function initMicrosite() {
    document.documentElement.classList.add('scroll-smooth');
    initStickyNav();
    initHeroSlider();
    initReviewsCarousel();
    initGalleryLightbox();
    initVideoModal();
    initFloatingScrollBehavior();
}
