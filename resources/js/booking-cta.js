/**
 * Affiche une barre d'appel à l'action flottante sur mobile pour la page de
 * réservation : elle apparaît une fois le hero dépassé et se masque dès que le
 * calendrier de réservation (#reserver) entre dans le viewport, pour ne pas
 * recouvrir l'action principale.
 */
(() => {
    const bar = document.querySelector('[data-booking-cta]');
    if (!bar) return;

    const target = document.querySelector('#reserver');
    const hero = document.querySelector('[data-booking-hero]');

    let pastHero = false;
    let atTarget = false;

    const update = () => {
        const show = pastHero && !atTarget;
        bar.classList.toggle('translate-y-0', show);
        bar.classList.toggle('opacity-100', show);
        bar.classList.toggle('translate-y-24', !show);
        bar.classList.toggle('opacity-0', !show);
        bar.classList.toggle('pointer-events-none', !show);
    };

    if (hero) {
        new IntersectionObserver(
            ([entry]) => {
                pastHero = !entry.isIntersecting;
                update();
            },
            { rootMargin: '0px 0px -100% 0px' },
        ).observe(hero);
    } else {
        pastHero = true;
    }

    if (target) {
        new IntersectionObserver(
            ([entry]) => {
                atTarget = entry.isIntersecting;
                update();
            },
            { rootMargin: '0px 0px -25% 0px' },
        ).observe(target);
    }

    update();
})();
