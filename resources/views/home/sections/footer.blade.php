@php
    $year = date('Y');
    $home = route('home');
    $nav = [
        'Accompagnement' => [
            ["Domaines d'accompagnement", $home.'#troubles'],
            ['Déroulé des séances', $home.'#methode'],
            ['Témoignages', $home.'#temoignages'],
        ],
        /**
         * Le catalogue n'apparaît qu'une fois une formation publiée : même
         * règle que le menu, pour ne pas renvoyer vers une page vide.
         */
        'Ressources' => array_values(array_filter([
            \App\Models\Course::hasPublished() ? ['Les formations', route('courses.index')] : null,
            ['Le blog', route('blog.index')],
            ['Les vidéos', route('videos.index')],
            ['Vidéo offerte', $home.'#capture'],
        ])),
        'À propos' => [
            ['Qui suis-je', route('about')],
            ['Contact', route('contact')],
            ['Prendre rendez-vous', route('booking.index')],
        ],
    ];
    $socialIcons = [
        'Instagram' => 'M7.5 2C4.46 2 2 4.46 2 7.5v9C2 19.54 4.46 22 7.5 22h9c3.04 0 5.5-2.46 5.5-5.5v-9C22 4.46 19.54 2 16.5 2h-9zM20 16.5c0 1.93-1.57 3.5-3.5 3.5h-9C5.57 20 4 18.43 4 16.5v-9C4 5.57 5.57 4 7.5 4h9C18.43 4 20 5.57 20 7.5v9zM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm5.5-9.25a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5z',
        'Facebook' => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.51 1.5-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.89h-2.33v6.99A10 10 0 0 0 22 12z',
        'YouTube' => 'M23.5 6.2a3 3 0 0 0-2.1-2.12C19.55 3.5 12 3.5 12 3.5s-7.55 0-9.4.58A3 3 0 0 0 .5 6.2C0 8.05 0 12 0 12s0 3.95.5 5.8a3 3 0 0 0 2.1 2.12c1.85.58 9.4.58 9.4.58s7.55 0 9.4-.58a3 3 0 0 0 2.1-2.12C24 15.95 24 12 24 12s0-3.95-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z',
        'TikTok' => 'M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-5.86 11.95 6.85 6.85 0 0 0 11.13-5.37V8.6a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.81-.03z',
        'LinkedIn' => 'M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.61 0 4.28 2.38 4.28 5.47v6.27zM5.34 7.43A2.06 2.06 0 1 1 5.33 3.3a2.06 2.06 0 0 1 .01 4.13zM7.12 20.45H3.56V9h3.56v11.45z',
    ];
    $socials = \App\Support\SiteContact::socials();
@endphp

<footer class="text-cream-100 relative overflow-hidden bg-teal-900">
    <div class="site-container relative pt-6 pb-5 lg:pt-12 lg:pb-10">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-4">
                <a href="/" class="inline-flex items-center" aria-label="Accueil">
                    <img
                        src="{{ asset('images/logo@2x.webp') }}"
                        alt="Laura Baechlé"
                        width="248"
                        height="96"
                        class="h-12 w-auto brightness-0 invert"
                    >
                </a>
                <p class="text-cream-100/70 mt-5 max-w-sm text-sm leading-relaxed">
                    Accompagnement ACT (thérapie d'acceptation et d'engagement) pour se libérer des troubles anxieux. À votre rythme, en toute bienveillance.
                </p>

                @if (! empty($socials))
                    <ul class="mt-6 flex items-center gap-3" aria-label="Réseaux sociaux">
                        @foreach ($socials as $name => $href)
                            <li>
                                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $name }}" class="text-cream-100 flex size-10 items-center justify-center rounded-full bg-white/5 ring-1 ring-white/10 transition hover:bg-white/10 hover:text-white">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="{{ $socialIcons[$name] }}"/>
                                    </svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <nav class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8" aria-label="Navigation pied de page">
                @foreach ($nav as $title => $links)
                    <div>
                        <h3 class="font-serif text-base font-medium text-white">{{ $title }}</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($links as [$label, $href])
                                <li>
                                    <a href="{{ $href }}" class="text-cream-100/70 transition hover:text-white">
                                        {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </div>

        {{-- Sur mobile, une grille à deux colonnes plutôt qu'un flex-wrap : les
             cinq intitulés ont des longueurs très inégales et s'enroulaient de
             façon imprévisible selon la largeur de l'écran. --}}
        <div class="mt-16 flex flex-col gap-6 border-t border-white/10 pt-8 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <ul class="text-cream-100/60 grid grid-cols-2 gap-x-4 text-xs sm:order-2 sm:flex sm:flex-wrap sm:items-center sm:gap-x-6">
                <li>
                    <a href="{{ route('legal.mentions') }}" class="block py-1.5 transition hover:text-white">Mentions légales</a>
                </li>
                <li>
                    <a href="{{ route('legal.privacy') }}" class="block py-1.5 transition hover:text-white">Politique de confidentialité</a>
                </li>
                <li>
                    <a href="{{ route('legal.cookies') }}" class="block py-1.5 transition hover:text-white">Politique cookies</a>
                </li>
                <li>
                    <a href="{{ route('legal.cgv') }}" class="block py-1.5 transition hover:text-white">CGV</a>
                </li>
                <li class="col-span-2 sm:col-span-1">
                    <a href="#" data-cookie-open class="block py-1.5 transition hover:text-white">Gérer les cookies</a>
                </li>
            </ul>
            <p class="text-cream-100/60 text-xs sm:order-1">
                © {{ $year }} Laura Baechlé · Vivre Pleinement. Tous droits réservés.
            </p>
        </div>
    </div>
</footer>
