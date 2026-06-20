/**
 * Parallaxe du paysage de nuages en pied de hero.
 *
 * Chaque bande porte data-parallax="<vitesse>" (montée au scroll) et, en option,
 * data-zoom="<facteur>" (grossissement des couches proches). Tout est écrit dans
 * un translate3d/scale calculé une fois par frame, atténué sur petit écran et
 * désactivé si l'utilisateur préfère réduire les animations.
 */
(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const layers = document.querySelectorAll('[data-parallax]');
    if (layers.length === 0) return;

    const items = Array.from(layers, (el) => ({
        el,
        speed: parseFloat(el.dataset.parallax) || 0,
        zoom: parseFloat(el.dataset.zoom) || 0,
    }));

    let damping = 1;
    const computeDamping = () => {
        const w = window.innerWidth;
        damping = w < 640 ? 0.4 : w < 1024 ? 0.65 : 1;
    };
    computeDamping();

    let ticking = false;

    const update = () => {
        const y = window.scrollY;
        for (const { el, speed, zoom } of items) {
            const scale = 1 + y * zoom * damping;
            el.style.transform = `translate3d(0, ${-y * speed * damping}px, 0) scale(${scale})`;
        }
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(update);
    }, { passive: true });

    window.addEventListener('resize', () => {
        computeDamping();
        update();
    }, { passive: true });

    update();
})();
