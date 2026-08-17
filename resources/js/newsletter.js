/**
 * Soumission fluide du formulaire d'inscription à la vidéo offerte.
 *
 * Progressive enhancement : sans JS, le formulaire POST classique fonctionne
 * et redirige vers #capture. Avec JS, on intercepte l'envoi en fetch et on
 * remplace le formulaire par le message de confirmation sans recharger la page.
 */
(() => {
    const form = document.querySelector('[data-newsletter-form]');
    if (!form) return;

    const errorBox = form.querySelector('[data-newsletter-error]');
    const submit = form.querySelector('button[type="submit"]');

    const showError = (message) => {
        if (!errorBox) return;
        errorBox.textContent = message;
        errorBox.hidden = false;
    };

    const clearError = () => {
        if (!errorBox) return;
        errorBox.textContent = '';
        errorBox.hidden = true;
    };

    const showSuccess = () => {
        const template = document.querySelector('[data-newsletter-success]');
        if (!template) return;
        form.replaceWith(template.content.cloneNode(true));
    };

    const firstError = (errors) => {
        const value = errors?.email ?? errors?.first_name;

        return Array.isArray(value) ? value[0] : value;
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError();
        submit.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            if (response.ok) {
                showSuccess();

                return;
            }

            const data = await response.json().catch(() => ({}));
            showError(firstError(data.errors) ?? 'Une erreur est survenue. Réessayez.');
            submit.disabled = false;
        } catch {
            showError('Connexion impossible. Vérifiez votre réseau et réessayez.');
            submit.disabled = false;
        }
    });
})();
