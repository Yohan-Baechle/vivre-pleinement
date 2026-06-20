/**
 * Lazy-load des embeds YouTube : l'iframe n'est injectée qu'au clic, depuis le
 * domaine youtube-nocookie.com. Zéro cookie tiers avant interaction (RGPD) et LCP
 * préservé.
 */
document.addEventListener('click', (e) => {
    const facade = e.target.closest('.youtube-facade');
    if (!facade) return;

    const { youtubeId, youtubeTitle } = facade.dataset;
    if (!youtubeId) return;

    const iframe = document.createElement('iframe');
    iframe.src = `https://www.youtube-nocookie.com/embed/${youtubeId}?autoplay=1&rel=0`;
    iframe.title = youtubeTitle || 'Vidéo YouTube';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;
    iframe.loading = 'lazy';
    iframe.className = 'absolute inset-0 size-full';

    facade.replaceChildren(iframe);
});
