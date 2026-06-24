import './cookies.js';
import './youtube-facade.js';
import './navbar.js';
import './parallax.js';
import './reveal.js';
import './seed-wind.js';
import './share.js';
import './booking-cta.js';
import './newsletter.js';

/**
 * Anime l'ouverture/fermeture des <details.accordion-item> via grid-template-rows.
 *
 * On s'appuie sur l'événement natif `toggle` pour l'ouverture (clavier + souris)
 * et on intercepte l'activation du summary à la fermeture pour jouer la transition
 * avant de retirer l'attribut `open`.
 */
(() => {
    const SELECTOR = '.accordion-item';

    const animateOpen = (details) => {
        const content = details.querySelector(':scope > .accordion-content');
        if (!content) return;

        content.style.gridTemplateRows = '0fr';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                content.style.gridTemplateRows = '';
            });
        });
    };

    const animateClose = (details) => {
        const content = details.querySelector(':scope > .accordion-content');
        if (!content) {
            details.open = false;
            return;
        }

        if (details.dataset.animating === 'true') return;
        details.dataset.animating = 'true';

        const onEnd = (ev) => {
            if (ev.target !== content || ev.propertyName !== 'grid-template-rows') return;
            content.removeEventListener('transitionend', onEnd);
            details.open = false;
            content.style.gridTemplateRows = '';
            delete details.dataset.animating;
        };
        content.addEventListener('transitionend', onEnd);

        content.style.gridTemplateRows = '1fr';
        void content.offsetHeight;
        content.style.gridTemplateRows = '0fr';
    };

    const interceptClose = (e) => {
        const summary = e.target.closest(`${SELECTOR} > summary`);
        if (!summary) return;

        const details = summary.parentElement;
        if (!details.open) return;

        e.preventDefault();
        animateClose(details);
    };

    document.addEventListener('click', interceptClose);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') interceptClose(e);
    });

    document.addEventListener('toggle', (e) => {
        const details = e.target;
        if (!details.matches?.(SELECTOR) || !details.open) return;
        animateOpen(details);
    }, true);
})();
