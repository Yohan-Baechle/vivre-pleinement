/**
 * Navbar « auto-hide » : le header se masque au scroll vers le bas et réapparaît
 * au scroll vers le haut. Toujours visible près du haut de page et tant que le
 * focus clavier reste à l'intérieur (accessibilité).
 */
(() => {
    const header = document.querySelector('[data-navbar]');
    if (!header) return;

    const HIDDEN_CLASS = '-translate-y-full';
    const TOP_OFFSET = 80;
    const DELTA = 6;

    let lastY = window.scrollY;
    let ticking = false;

    const update = () => {
        const y = window.scrollY;
        const diff = y - lastY;

        if (Math.abs(diff) < DELTA) {
            ticking = false;
            return;
        }

        if (y <= TOP_OFFSET || diff < 0) {
            header.classList.remove(HIDDEN_CLASS);
        } else if (!header.contains(document.activeElement)) {
            header.classList.add(HIDDEN_CLASS);
        }

        lastY = y;
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });

    header.addEventListener('focusin', () => header.classList.remove(HIDDEN_CLASS));

    /**
     * Menu mobile (<details>) : se ferme après un clic sur un lien de navigation,
     * au clic en dehors du menu, et à la touche Échap.
     */
    const mobileNav = header.querySelector('details[name="mobile-nav"]');
    if (mobileNav) {
        const closeMenu = () => { mobileNav.open = false; };

        mobileNav.querySelectorAll('ul a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('click', (e) => {
            if (mobileNav.open && !mobileNav.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.open) {
                closeMenu();
            }
        });
    }
})();
