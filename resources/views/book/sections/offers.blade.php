<x-section id="offres" bg="bg-cream-50" eyebrow="Choisissez votre formule" title="Deux façons de commencer." lead="Le livre seul, ou le livre accompagné d'une heure de coaching personnalisée avec moi.">
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">

        <article class="ring-ink/5 relative flex flex-col rounded-4xl bg-white p-8 shadow-xs ring-1 sm:p-10">
            <div>
                <p class="text-ink-muted text-xs font-medium tracking-wider uppercase">Formule essentielle</p>
                <h3 class="text-ink mt-2 font-serif text-2xl font-medium sm:text-3xl">Le livre seul</h3>
                <p class="text-ink-soft mt-3 text-sm sm:text-base">Le guide complet, 77 pages, 12 fiches pratiques. Pour avancer à votre rythme, en autonomie.</p>
            </div>

            <div class="mt-6 flex items-baseline gap-2">
                <span class="text-ink font-serif text-5xl font-medium">{!! $offerPrice($offerSolo) !!}</span>
                <span class="text-ink-muted text-sm">TTC · paiement unique</span>
            </div>

            <ul class="text-ink-soft mt-7 space-y-3 text-sm sm:text-base">
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Le livre complet en PDF · 77 pages
                </li>
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    12 fiches pratiques à imprimer
                </li>
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Lisible sur mobile, ordi, liseuse
                </li>
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Téléchargement immédiat par email
                </li>
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Support par email pendant votre lecture
                </li>
            </ul>

            <div class="mt-8">
                <x-book-offer-cta
                    slug="livre"
                    :available="$offerAvailable('livre')"
                    :label="'Obtenir le livre · '.$offerPrice($offerSolo)"
                    class="bg-ink shadow-ink/20 inline-flex w-full items-center justify-center gap-2 rounded-full px-7 py-3.5 text-sm font-medium text-white shadow-lg transition hover:bg-teal-800 sm:text-base" />
            </div>
        </article>

        <article class="relative flex flex-col rounded-4xl bg-linear-to-br from-teal-700 to-teal-800 p-8 text-white shadow-2xl shadow-teal-700/20 sm:p-10">
            <span class="bg-rose-soft absolute -top-3 left-1/2 -translate-x-1/2 rounded-full px-4 py-1 text-xs font-semibold tracking-wider text-teal-900 uppercase shadow-sm">
                ✨ Recommandé
            </span>

            <div>
                <p class="text-xs font-medium tracking-wider text-teal-100 uppercase">Formule accompagnée</p>
                <h3 class="mt-2 font-serif text-2xl font-medium sm:text-3xl">Le livre + 1h de coaching</h3>
                <p class="mt-3 text-sm text-teal-50 sm:text-base">Le livre, et une heure en visio ou par téléphone avec moi pour adapter la méthode à votre situation précise.</p>
            </div>

            <div class="mt-6 flex items-baseline gap-2">
                <span class="font-serif text-5xl font-medium">{!! $offerPrice($offerCoaching) !!}</span>
                <span class="text-sm text-teal-100">TTC · paiement unique</span>
            </div>

            <ul class="mt-7 space-y-3 text-sm text-teal-50 sm:text-base">
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    <strong class="font-medium text-white">Tout ce que contient la formule essentielle</strong>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    <strong class="font-medium text-white">1h de coaching individuel</strong> en visio ou téléphone
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Plan d'action personnalisé pour votre situation
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Cadre confidentiel, sans jugement
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    À planifier quand vous êtes prêt(e)
                </li>
            </ul>

            <div class="mt-8">
                <x-book-offer-cta
                    slug="livre-coaching"
                    :available="$offerAvailable('livre-coaching')"
                    :label="'Obtenir le livre + coaching · '.$offerPrice($offerCoaching)"
                    class="hover:bg-cream-50 inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-teal-800 shadow-lg transition sm:text-base" />
            </div>
        </article>
    </div>

    @php $anyAvailable = $offerAvailable('livre') || $offerAvailable('livre-coaching'); @endphp

    @if ($anyAvailable)
        <p class="text-ink-muted mt-10 text-center text-xs sm:text-sm">
            Paiement sécurisé par Stripe et PayPal · Aucun renouvellement, aucun abonnement caché
        </p>
    @else
        {{-- Rien n'est encore en vente : une seule invitation, et surtout pas
             la mention « paiement sécurisé » qui laisserait croire le
             contraire. --}}
        <div class="mt-10 text-center">
            <a href="{{ route('contact') }}"
               class="group text-ink ring-ink/10 hover:bg-cream-50 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium ring-1 transition sm:text-base">
                Être prévenu de la sortie
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
            <p class="text-ink-muted mt-4 text-xs sm:text-sm">
                Le livre est en cours de finalisation. Laissez-moi un mot, je vous préviens dès sa mise en ligne.
            </p>
        </div>
    @endif
</x-section>
