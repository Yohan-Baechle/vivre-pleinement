/**
 * Boutons « Copier le lien » de partage d'article.
 *
 * Au clic : copie l'URL dans le presse-papier, puis bascule le bouton en état
 * « copié » (coche + tooltip) pendant 2 s avant de revenir à l'icône de copie.
 * Le retour visuel est piloté par l'attribut `data-copied` (false/true), sur
 * lequel s'appuient les utilitaires Tailwind du markup.
 */
(() => {
    const RESET_DELAY = 2000;
    const timers = new WeakMap();

    const markCopied = (button) => {
        button.setAttribute('data-copied', 'true');

        clearTimeout(timers.get(button));
        timers.set(
            button,
            setTimeout(() => button.setAttribute('data-copied', 'false'), RESET_DELAY),
        );
    };

    const copy = async (button) => {
        const url = button.dataset.copyUrl;
        if (!url) return;

        try {
            await navigator.clipboard.writeText(url);
        } catch {
            // Repli pour les contextes sans Clipboard API (http, anciens navigateurs).
            const field = document.createElement('textarea');
            field.value = url;
            field.setAttribute('readonly', '');
            field.style.position = 'absolute';
            field.style.left = '-9999px';
            document.body.appendChild(field);
            field.select();
            try {
                document.execCommand('copy');
            } catch {
                document.body.removeChild(field);
                return;
            }
            document.body.removeChild(field);
        }

        markCopied(button);
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-copy-url]');
        if (button) {
            copy(button);
        }
    });
})();
