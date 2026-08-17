/**
 * Tiroir (drawer) des filtres du blog en version mobile.
 *
 * Ouverture/fermeture animées, piège à focus (accessibilité clavier) tant que
 * le tiroir est ouvert, fermeture à l'Échap ou au clic sur le fond, et
 * soumission automatique des filtres au changement de valeur des <select>.
 */
(() => {
    const drawer = document.getElementById('filters-drawer');
    if (!drawer) return;

    const panel = drawer.querySelector('[data-drawer-panel]');
    const focusableSel = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
    let lastFocused = null;

    const trapFocus = (e) => {
        if (e.key !== 'Tab') return;
        const focusables = panel.querySelectorAll(focusableSel);
        if (!focusables.length) return;
        const first = focusables[0];
        const last = focusables[focusables.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    };

    const open = () => {
        lastFocused = document.activeElement;
        drawer.classList.remove('invisible');
        document.body.classList.add('overflow-hidden');
        drawer.setAttribute('aria-modal', 'true');
        drawer.setAttribute('role', 'dialog');
        requestAnimationFrame(() => requestAnimationFrame(() => {
            drawer.classList.remove('opacity-0');
            panel.classList.remove('translate-x-full');
            panel.querySelector(focusableSel)?.focus({ preventScroll: true });
        }));
        document.addEventListener('keydown', trapFocus);
    };

    const close = () => {
        drawer.classList.add('opacity-0');
        panel.classList.add('translate-x-full');
        document.body.classList.remove('overflow-hidden');
        document.removeEventListener('keydown', trapFocus);
        setTimeout(() => drawer.classList.add('invisible'), 300);
        lastFocused?.focus();
    };

    document.querySelectorAll('[data-drawer-open]').forEach((el) => el.addEventListener('click', open));
    document.querySelectorAll('[data-drawer-close]').forEach((el) => el.addEventListener('click', close));
    document.addEventListener('keydown', (e) => e.key === 'Escape' && !drawer.classList.contains('invisible') && close());

    document.querySelectorAll('[data-auto-submit] select').forEach((select) => {
        select.addEventListener('change', () => select.form.submit());
    });
})();
