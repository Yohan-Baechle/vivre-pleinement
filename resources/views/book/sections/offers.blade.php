<x-section id="offres" bg="bg-white" eyebrow="Choisissez votre formule" title="Et si vous achetiez mon guide maintenant ?" lead="Vous pouvez le télécharger immédiatement et vous le recevrez tout de suite par email.">
    {{-- Subgrid : les deux cartes partagent les mêmes lignes (titre, accroche,
         prix, liste, bouton) pour que tout reste aligné malgré des textes de
         longueurs différentes. --}}
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-2 lg:grid-rows-[auto_auto_auto_1fr_auto] lg:gap-x-8 lg:gap-y-0">

        <article class="relative grid grid-rows-[auto_auto_auto_1fr_auto] rounded-4xl bg-linear-to-br from-teal-700 to-teal-800 p-8 text-white shadow-2xl shadow-teal-700/20 sm:p-10 lg:row-span-5 lg:grid-rows-subgrid">
            <span class="bg-rose-soft absolute -top-3 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full px-4 py-1 text-xs font-semibold tracking-wider text-teal-900 uppercase shadow-sm">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2.5l2.7 6.06 6.6.62-4.96 4.4 1.44 6.47L12 16.8l-5.78 3.25 1.44-6.47L2.7 9.18l6.6-.62L12 2.5z"/>
                </svg>
                Recommandé
            </span>

            <h3 class="font-serif text-2xl font-medium sm:text-3xl">Livre + accompagnement</h3>
            <p class="mt-3 text-sm text-teal-50 sm:text-base">Le livre, et une heure avec moi par téléphone ou en visio. Parce que l'information seule ne suffit pas : il vous faut un encadrement pour vous investir.</p>

            <div class="mt-6 flex items-baseline gap-2">
                <span class="font-serif text-5xl font-medium">{!! $offerPrice($offerCoaching) !!}</span>
                <span class="text-sm text-teal-100">TTC · paiement unique</span>
            </div>

            <ul class="mt-7 space-y-3 text-sm text-teal-50 sm:text-base">
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    <span>Mon livre <em>Soigner le TOC de la phobie d'impulsion à l'aide de traitements naturels</em></span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    <span><strong class="font-medium text-white">Un accompagnement d'une heure avec moi</strong>, par téléphone ou en visio</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Un suivi par mail pour toutes vos questions
                </li>
                <li class="flex items-start gap-3">
                    <svg class="text-rose-soft mt-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Téléchargement immédiat par email
                </li>
            </ul>

            <div class="pt-8">
                <x-book-offer-cta
                    slug="livre-coaching"
                    :available="$offerAvailable('livre-coaching')"
                    :label="'Obtenir le livre + l\'accompagnement · '.$offerPrice($offerCoaching)"
                    class="hover:bg-cream-50 inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-teal-800 shadow-lg transition sm:text-base" />
            </div>
        </article>

        <article class="ring-ink/5 bg-cream-50 relative grid grid-rows-[auto_auto_auto_1fr_auto] rounded-4xl p-8 shadow-xs ring-1 sm:p-10 lg:row-span-5 lg:grid-rows-subgrid">
            <h3 class="text-ink font-serif text-2xl font-medium sm:text-3xl">Livre seul</h3>
            <p class="text-ink-soft mt-3 text-sm sm:text-base">Le guide complet, 77 pages, 12 fiches pratiques. Pour avancer à votre rythme, en autonomie.</p>

            <div class="mt-6 flex items-baseline gap-2">
                <span class="text-ink font-serif text-5xl font-medium">{!! $offerPrice($offerSolo) !!}</span>
                <span class="text-ink-muted text-sm">TTC · paiement unique</span>
            </div>

            <ul class="text-ink-soft mt-7 space-y-3 text-sm sm:text-base">
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    <span>Mon livre <em>Soigner le TOC de la phobie d'impulsion à l'aide de traitements naturels</em></span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Un suivi par mail pour toutes vos questions
                </li>
                <li class="flex items-start gap-3">
                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>
                    Téléchargement immédiat par email
                </li>
            </ul>

            <div class="pt-8">
                <x-book-offer-cta
                    slug="livre"
                    :available="$offerAvailable('livre')"
                    :label="'Obtenir le livre uniquement · '.$offerPrice($offerSolo)"
                    class="bg-ink shadow-ink/20 inline-flex w-full items-center justify-center gap-2 rounded-full px-7 py-3.5 text-sm font-medium text-white shadow-lg transition hover:bg-teal-800 sm:text-base" />
            </div>
        </article>
    </div>

    @php $anyAvailable = $offerAvailable('livre') || $offerAvailable('livre-coaching'); @endphp

    @if ($anyAvailable)
        <p class="text-ink-soft mt-10 text-center text-sm sm:text-base">
            Vous avez des questions ? Vous trouverez une <a href="#faq" class="border-b border-teal-700/30 text-teal-700">FAQ (Foire aux questions)</a> en bas de cette page.
        </p>

        <p class="text-ink-muted mt-3 text-center text-xs sm:text-sm">
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
