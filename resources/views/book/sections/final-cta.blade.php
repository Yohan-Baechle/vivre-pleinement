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
            <span class="size-1.5 rounded-full bg-teal-500"></span>
            Dernière chose
        </p>

        <h2 class="text-ink mt-6 font-serif text-3xl leading-tight font-medium tracking-tight sm:text-4xl lg:text-5xl">
            Vous avez déjà attendu trop longtemps.
        </h2>

        <div class="text-ink-soft mt-6 space-y-4 text-base sm:text-lg">
            <p>
                Si vous êtes arrivé(e) jusqu'ici, c'est que quelque chose en vous reconnaît ce dont je parle.
            </p>
            <p>
                Ces pensées peuvent continuer à voler des jours, des mois, des années de votre vie. Ou vous pouvez tourner la première page ce soir.
            </p>
        </div>

        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('book.checkout', 'livre') }}" class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:w-auto sm:text-base">
                Obtenir le livre · 37&nbsp;€
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
            <a href="{{ route('book.checkout', 'livre-coaching') }}" class="group hover:bg-cream-50 inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-teal-800 shadow-xs ring-1 ring-teal-200 transition sm:w-auto sm:text-base">
                Livre + coaching · 70&nbsp;€
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
        </div>

        <p class="text-ink-muted mt-8 text-xs sm:text-sm">
            Téléchargement immédiat · Paiement sécurisé · Garantie 30 jours
        </p>
    </div>
</section>
