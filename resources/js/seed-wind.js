/**
 * Graines de pissenlit emportées par le vent, pilotées par le scroll.
 *
 * Pour chaque [data-seed], on calcule une progression p (0 quand la graine
 * entre par le bas du viewport, 1 quand elle sort par le haut) puis on en dérive
 * une transform : la graine entre plus grosse, monte, rétrécit, dérive
 * latéralement et pivote légèrement — comme happée au loin.
 *
 * data-seed-drift : amplitude latérale en px
 * data-seed-spin  : rotation max en deg
 * data-seed-depth : facteur de vitesse verticale (profondeur)
 *
 * Désactivé si l'utilisateur préfère réduire les animations.
 */
(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const els = document.querySelectorAll('[data-seed]');
    if (els.length === 0) return;

    const seeds = Array.from(els, (el) => ({
        el,
        drift: parseFloat(el.dataset.seedDrift) || 0,
        spin: parseFloat(el.dataset.seedSpin) || 0,
        depth: parseFloat(el.dataset.seedDepth) || 1,
    }));

    // Courbe douce (ease-in-out) pour atténuer les extrêmes.
    const ease = (t) => t * t * (3 - 2 * t);

    let ticking = false;

    const update = () => {
        const vh = window.innerHeight;
        for (const { el, drift, spin, depth } of seeds) {
            const rect = el.getBoundingClientRect();
            const center = rect.top + rect.height / 2;

            // p : 0 quand le centre est en bas du viewport, 1 quand il sort en haut.
            let p = 1 - center / vh;
            if (p < 0) p = 0;
            else if (p > 1) p = 1;

            const e = ease(p);

            // Montée amplifiée (vent qui aspire vers le haut).
            const ty = -e * 90 * depth;
            // Dézoom : grosse à l'entrée (1.25), petite en sortie (~0.55).
            const scale = 1.25 - e * 0.7;
            // Dérive latérale en cloche (max au milieu du parcours).
            const tx = drift * Math.sin(p * Math.PI);
            // Rotation progressive.
            const rot = spin * e;

            el.style.transform =
                `translate3d(${tx.toFixed(1)}px, ${ty.toFixed(1)}px, 0) rotate(${rot.toFixed(1)}deg) scale(${scale.toFixed(3)})`;
        }
        ticking = false;
    };

    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(update);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
})();
