<section class="from-cream-50 relative overflow-hidden bg-linear-to-b via-teal-50 to-teal-100 py-20 sm:py-24 lg:py-28">
    <div class="pointer-events-none absolute inset-0 -z-0 overflow-hidden">
        <div class="cloud-r cloud-d-160 absolute top-20 -left-40">
            <div class="cloud-sway cloud-s-15 drop-shadow-cloud-md text-white">
                <svg class="size-28" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true">
                    <path d="M160.06,40A88.1,88.1,0,0,0,81.29,88.67h0A87.48,87.48,0,0,0,72,127.73,8.18,8.18,0,0,1,64.57,136,8,8,0,0,1,56,128a103.66,103.66,0,0,1,5.34-32.92,4,4,0,0,0-4.75-5.18A64.09,64.09,0,0,0,8,152c0,35.19,29.75,64,65,64H160a88.09,88.09,0,0,0,87.93-91.48C246.11,77.54,207.07,40,160.06,40Z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="relative mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-10">
        <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
            Dernière chose
        </p>

        <h2 class="text-ink mt-6 font-serif text-3xl leading-tight font-medium tracking-tight sm:text-4xl lg:text-5xl">
            Et si vous achetiez mon guide maintenant ?
        </h2>

        <div class="text-ink-soft mt-6 space-y-4 text-base sm:text-lg">
            <p>Vous pouvez le télécharger immédiatement&nbsp;:</p>
        </div>

        {{-- Bloc de conversion, pas de comparaison : tant que rien n'est en
             vente, il porte une seule invitation plutôt que deux formules
             grisées. --}}
        @if ($offerAvailable('livre') || $offerAvailable('livre-coaching'))
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <div class="w-full sm:w-auto">
                    <x-book-offer-cta
                        slug="livre"
                        :available="$offerAvailable('livre')"
                        :label="'Obtenir le livre uniquement · '.$offerPrice($offerSolo)"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:w-auto sm:text-base" />
                </div>
                <div class="w-full sm:w-auto">
                    <x-book-offer-cta
                        slug="livre-coaching"
                        :available="$offerAvailable('livre-coaching')"
                        :label="'Obtenir le livre + l\'accompagnement · '.$offerPrice($offerCoaching)"
                        class="hover:bg-cream-50 inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-teal-800 shadow-xs ring-1 ring-teal-200 transition sm:w-auto sm:text-base" />
                </div>
            </div>

            <p class="text-ink-soft mt-8 text-sm sm:text-base">
                Dès que vous voyez les premiers résultats, envoyez-moi un mail à l'adresse
                <a href="mailto:{{ \App\Support\SiteContact::email() }}" class="border-b border-teal-700/30 text-teal-700">{{ \App\Support\SiteContact::email() }}</a>
            </p>
        @else
            <div class="mt-10">
                <a href="{{ route('contact') }}"
                   class="group inline-flex items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                    Être prévenu de la sortie
                    <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
            </div>

            <p class="text-ink-muted mt-8 text-xs sm:text-sm">
                Sortie imminente. Aucun engagement, je vous écris une seule fois.
            </p>
        @endif
    </div>
</section>
