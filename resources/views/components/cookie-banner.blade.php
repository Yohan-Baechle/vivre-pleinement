<div id="cookie-banner" data-cookie-banner role="dialog" aria-modal="false" aria-labelledby="cookie-banner-title"
     class="fixed inset-x-0 bottom-0 z-[60] hidden translate-y-4 opacity-0 transition duration-300 ease-out data-[visible=true]:translate-y-0 data-[visible=true]:opacity-100">
    <div class="mx-auto max-w-4xl px-3 pb-3 sm:px-6 sm:pb-5">
        <div class="ring-ink/10 rounded-2xl bg-white p-4 shadow-2xl ring-1 sm:p-5">
            {{-- Bannière principale : barre compacte, le refus reste aussi simple que l'acceptation (CNIL) --}}
            <div data-cookie-view="banner" class="sm:flex sm:items-center sm:justify-between sm:gap-6">
                <p id="cookie-banner-title" class="text-ink-soft text-xs leading-snug sm:text-sm">
                    <strong class="text-ink font-medium">Cookies&nbsp;:</strong>
                    fonctionnement du site et, si vous l'acceptez, mesure d'audience.
                    <a href="{{ route('legal.cookies') }}" class="text-teal-700 underline-offset-4 hover:text-teal-800 hover:underline">En savoir plus</a>
                </p>
                <div class="mt-3 flex shrink-0 items-center gap-2 sm:mt-0">
                    <button type="button" data-cookie-action="accept"
                            class="inline-flex flex-1 items-center justify-center rounded-full bg-teal-700 px-4 py-2 text-xs font-medium text-white shadow-sm shadow-teal-700/20 transition hover:bg-teal-800 sm:flex-none sm:text-sm">
                        Tout accepter
                    </button>
                    <button type="button" data-cookie-action="reject"
                            class="text-ink-soft ring-ink/15 hover:bg-cream-100 hover:text-ink inline-flex flex-1 items-center justify-center rounded-full bg-white px-4 py-2 text-xs font-medium ring-1 transition sm:flex-none sm:text-sm">
                        Tout refuser
                    </button>
                    <button type="button" data-cookie-action="customize"
                            class="text-ink-muted hover:text-ink shrink-0 px-1 text-xs font-medium underline decoration-current/30 underline-offset-4 transition sm:text-sm">
                        Personnaliser
                    </button>
                </div>
            </div>

            {{-- Personnalisation par catégorie --}}
            <div data-cookie-view="customize" hidden>
                <p class="text-ink font-serif text-xl font-medium">Personnaliser mes cookies</p>
                <p class="text-ink-soft mt-2 text-sm">Choisissez les catégories que vous acceptez.</p>

                <ul class="mt-5 space-y-3">
                    <li class="bg-cream-50 ring-ink/5 rounded-2xl p-4 ring-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-ink text-sm font-medium">Cookies essentiels</p>
                                <p class="text-ink-soft mt-1 text-xs">Indispensables au fonctionnement du site (session, sécurité, mémorisation de vos préférences).</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700 ring-1 ring-teal-200">Toujours actifs</span>
                        </div>
                    </li>

                    <li class="bg-cream-50 ring-ink/5 rounded-2xl p-4 ring-1">
                        <label class="flex cursor-pointer items-start justify-between gap-4">
                            <div>
                                <p class="text-ink text-sm font-medium">Mesure d'audience</p>
                                <p class="text-ink-soft mt-1 text-xs">Google Analytics - nous aide à comprendre comment vous utilisez le site, de manière anonymisée.</p>
                            </div>
                            <input type="checkbox" data-cookie-category="analytics"
                                   class="border-ink/20 mt-1 size-5 shrink-0 rounded-sm text-teal-700 focus:ring-2 focus:ring-teal-500">
                        </label>
                    </li>
                </ul>

                <div class="mt-5 flex flex-wrap items-center gap-2 sm:gap-3">
                    <button type="button" data-cookie-action="save"
                            class="inline-flex flex-1 items-center justify-center rounded-full bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-teal-700/20 transition hover:bg-teal-800 sm:flex-none">
                        Enregistrer mes choix
                    </button>
                    <button type="button" data-cookie-action="back"
                            class="text-ink-soft ring-ink/15 hover:bg-cream-100 hover:text-ink inline-flex items-center justify-center rounded-full bg-white px-5 py-2.5 text-sm font-medium ring-1 transition">
                        ← Retour
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-cookie-open]').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('cookies:reopen'));
            });
        });
    });
</script>
