import { loadStripe } from '@stripe/stripe-js';

/**
 * Paiement intégré (Stripe Payment Element) sur la page /reservation/payer.
 * Ne s'active que si le conteneur #payment-form est présent ; configuration et
 * appearance (charte teal) lues depuis ses attributs data-*.
 */
async function initStripePayment() {
    const form = document.getElementById('payment-form');
    if (!form) return;

    const { stripeKey, clientSecret, returnUrl, amountLabel } = form.dataset;
    if (!stripeKey || !clientSecret) return;

    const stripe = await loadStripe(stripeKey);

    const appearance = {
        theme: 'flat',
        variables: {
            colorPrimary: '#0f766e',
            colorText: '#1f2937',
            colorTextSecondary: '#6b7280',
            colorBackground: '#ffffff',
            colorDanger: '#be123c',
            borderRadius: '14px',
            fontFamily: '"Instrument Sans", system-ui, sans-serif',
            fontSizeBase: '15px',
            spacingUnit: '4px',
            spacingGridRow: '18px',
            focusBoxShadow: '0 0 0 2px rgba(15, 118, 110, 0.25)',
            focusOutline: 'none',
        },
        rules: {
            '.Input': {
                border: '1px solid rgba(15, 23, 42, 0.1)',
                boxShadow: 'none',
                padding: '12px',
            },
            '.Input:focus': {
                border: '1px solid #0f766e',
                boxShadow: '0 0 0 2px rgba(15, 118, 110, 0.25)',
            },
            '.Label': {
                fontWeight: '500',
                marginBottom: '6px',
            },
            '.Tab': {
                border: '1px solid rgba(15, 23, 42, 0.1)',
                boxShadow: 'none',
            },
            '.Tab:hover': {
                border: '1px solid rgba(15, 118, 110, 0.4)',
            },
            '.Tab:focus': {
                boxShadow: '0 0 0 2px rgba(15, 118, 110, 0.25)',
            },
            '.Tab--selected': {
                border: '1px solid #0f766e',
                backgroundColor: '#0f766e',
                color: '#ffffff',
            },
            '.Tab--selected:focus': {
                border: '1px solid #0f766e',
                boxShadow: '0 0 0 2px rgba(15, 118, 110, 0.25)',
            },
            '.TabIcon--selected': {
                fill: '#ffffff',
            },
            '.TabLabel--selected': {
                color: '#ffffff',
            },
        },
    };

    const elements = stripe.elements({ clientSecret, appearance });
    const paymentElement = elements.create('payment', { layout: 'tabs' });
    paymentElement.mount('#payment-element');

    paymentElement.on('ready', () => {
        document.getElementById('payment-skeleton')?.remove();
    });

    const submitButton = document.getElementById('payment-submit');
    const errorBox = document.getElementById('payment-error');
    const buttonLabel = document.getElementById('payment-submit-label');
    const buttonSpinner = document.getElementById('payment-submit-spinner');

    const setLoading = (loading) => {
        submitButton.disabled = loading;
        buttonSpinner?.classList.toggle('hidden', !loading);
        if (buttonLabel) {
            const defaultLabel = amountLabel ? `Payer ${amountLabel}` : 'Payer';
            buttonLabel.textContent = loading ? 'Paiement en cours…' : defaultLabel;
        }
    };

    const showError = (message) => {
        if (!errorBox) return;
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox?.classList.add('hidden');
        setLoading(true);

        const { error } = await stripe.confirmPayment({
            elements,
            confirmParams: { return_url: returnUrl },
        });

        if (error) {
            showError(error.message || 'Le paiement a échoué. Vérifiez vos informations et réessayez.');
            setLoading(false);
        }
    });
}

initStripePayment();
