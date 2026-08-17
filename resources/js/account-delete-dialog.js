/**
 * Modale de confirmation de suppression de compte.
 *
 * Le bouton de suppression reste désactivé tant que l'utilisateur n'a pas
 * saisi « SUPPRIMER » dans le champ de confirmation. La modale se réinitialise
 * à chaque ouverture ainsi qu'à sa fermeture (bouton annuler, clic sur le
 * fond, ou fermeture native de la <dialog>).
 */
(() => {
    const dialog = document.getElementById('delete-account-dialog');
    if (!dialog) return;

    const openBtn = document.querySelector('[data-open-delete-dialog]');
    const closeBtn = dialog.querySelector('[data-close-delete-dialog]');
    const input = dialog.querySelector('[data-delete-confirm]');
    const submit = dialog.querySelector('[data-delete-submit]');

    const reset = () => {
        input.value = '';
        submit.disabled = true;
    };

    openBtn?.addEventListener('click', () => {
        reset();
        dialog.showModal();
        input.focus();
    });

    closeBtn?.addEventListener('click', () => dialog.close());

    input?.addEventListener('input', () => {
        submit.disabled = input.value.trim().toUpperCase() !== 'SUPPRIMER';
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    dialog.addEventListener('close', reset);

    if (dialog.hasAttribute('data-reopen')) {
        dialog.showModal();
        input?.focus();
    }
})();
