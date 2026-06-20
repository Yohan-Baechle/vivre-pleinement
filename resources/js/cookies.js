/**
 * Consentement cookies conforme CNIL (délibération 2020-091) : choix accept /
 * reject / customize équivalents, persistance localStorage versionnée, et aucun
 * script tiers chargé avant consentement explicite.
 *
 * API publique : window.cookieConsent.get(category) -> boolean
 */
(() => {
    const STORAGE_KEY = 'cookie-consent';
    const POLICY_VERSION = 1;
    const CATEGORIES = ['analytics'];

    const banner = document.querySelector('[data-cookie-banner]');
    if (!banner) return;

    const views = {
        banner: banner.querySelector('[data-cookie-view="banner"]'),
        customize: banner.querySelector('[data-cookie-view="customize"]'),
    };

    const load = () => {
        try {
            const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY));
            return parsed?.version === POLICY_VERSION ? parsed : null;
        } catch {
            return null;
        }
    };

    const save = (choices) => {
        const payload = {
            version: POLICY_VERSION,
            date: new Date().toISOString(),
            choices,
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
        applyChoices(choices);
        window.dispatchEvent(new CustomEvent('cookies:consent', { detail: payload }));
    };

    const applyChoices = (choices) => {
        if (choices.analytics) {
            loadAnalytics();
        }
    };

    const loadAnalytics = () => {
        const gaId = document.querySelector('meta[name="ga-id"]')?.content;
        if (!gaId || window.__gaLoaded) return;
        window.__gaLoaded = true;

        const script = document.createElement('script');
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', gaId, { anonymize_ip: true });
    };

    const show = () => {
        banner.classList.remove('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => banner.dataset.visible = 'true');
        });
    };

    const hide = () => {
        banner.dataset.visible = 'false';
        setTimeout(() => banner.classList.add('hidden'), 300);
    };

    const switchView = (name) => {
        Object.entries(views).forEach(([key, el]) => {
            el.hidden = key !== name;
        });
    };

    const readChoices = () => Object.fromEntries(
        CATEGORIES.map((c) => {
            const checkbox = banner.querySelector(`[data-cookie-category="${c}"]`);
            return [c, Boolean(checkbox?.checked)];
        })
    );

    const allChoices = (value) => Object.fromEntries(CATEGORIES.map((c) => [c, value]));

    banner.addEventListener('click', (e) => {
        const action = e.target.closest('[data-cookie-action]')?.dataset.cookieAction;
        if (!action) return;

        switch (action) {
            case 'accept':
                save(allChoices(true));
                hide();
                break;
            case 'reject':
                save(allChoices(false));
                hide();
                break;
            case 'save':
                save(readChoices());
                hide();
                break;
            case 'customize':
                switchView('customize');
                break;
            case 'back':
                switchView('banner');
                break;
        }
    });

    window.addEventListener('cookies:reopen', () => {
        const stored = load();
        if (stored) {
            CATEGORIES.forEach((c) => {
                const checkbox = banner.querySelector(`[data-cookie-category="${c}"]`);
                if (checkbox) checkbox.checked = Boolean(stored.choices[c]);
            });
            switchView('customize');
        } else {
            switchView('banner');
        }
        show();
    });

    window.cookieConsent = {
        get: (category) => Boolean(load()?.choices?.[category]),
    };

    const stored = load();
    if (stored) {
        applyChoices(stored.choices);
    } else {
        show();
    }
})();
