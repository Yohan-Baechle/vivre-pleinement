/**
 * Apparition au scroll des éléments [data-reveal] et des étapes de frise.
 */
(() => {
    const SELECTOR = '[data-reveal], .timeline-step';
    const targets = document.querySelectorAll(SELECTOR);
    if (targets.length === 0) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            entry.target.classList.add('is-revealed');
            obs.unobserve(entry.target);
        }
    }, {
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.15,
    });

    targets.forEach((el) => observer.observe(el));
})();
