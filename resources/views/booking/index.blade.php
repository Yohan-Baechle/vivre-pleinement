@extends('layouts.site')

@section('title', 'Prendre rendez-vous · Accompagnement ACT | Laura Baechlé')
@section('description', "Accompagnement individuel en thérapie d'acceptation et d'engagement (ACT) pour vous libérer de vos troubles anxieux. Par téléphone ou en visio, avec Laura Baechlé.")
@section('canonical', route('booking.index'))

@push('head')
    @php
        $offers = $services->map(fn ($s) => [
            '@type' => 'Offer',
            'name' => $s->name,
            'price' => number_format($s->price, 2, '.', ''),
            'priceCurrency' => $s->currency,
            'url' => route('booking.show', $s->slug),
            'availability' => 'https://schema.org/InStock',
        ])->all();
        $bookingLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Accompagnement des troubles anxieux par l\'ACT',
            'serviceType' => 'Accompagnement ACT',
            'provider' => [
                '@type' => 'Person',
                'name' => 'Laura Baechlé',
                'jobTitle' => 'Praticienne ACT',
                'url' => route('home'),
            ],
            'areaServed' => ['@type' => 'Country', 'name' => 'France'],
            'availableChannel' => [
                '@type' => 'ServiceChannel',
                'serviceUrl' => route('booking.index'),
                'serviceLocation' => ['@type' => 'VirtualLocation', 'name' => 'Téléphone ou visioconférence'],
            ],
            'offers' => $offers,
        ];

        $bookingFaq = \App\Support\BookingFaq::all();
        $faqLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            '@id' => route('booking.index').'#faq',
            'url' => route('booking.index'),
            'inLanguage' => 'fr-FR',
            'mainEntity' => collect($bookingFaq)->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($bookingLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main id="main">

    {{-- Hero --}}
    <header data-booking-hero class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Prendre rendez-vous'],
            ]" />

            <div class="mt-8 grid items-center gap-12 lg:mt-10 lg:grid-cols-12 lg:gap-10">
                {{-- Colonne texte --}}
                <div class="text-center lg:col-span-6 lg:text-left">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                        <span class="size-1.5 rounded-full bg-teal-500"></span>
                        Thérapie d'acceptation et d'engagement (ACT)
                    </p>
                    <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                        Vous pouvez vous libérer de vos troubles anxieux.
                    </h1>
                    <p class="text-ink-soft mt-5 text-base sm:text-lg lg:max-w-xl">
                        Laissez-moi vous accompagner sur le chemin de la guérison, avec un suivi individuel,
                        personnalisé et unique, par téléphone ou en visio.
                    </p>

                    <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:flex-wrap lg:justify-start">
                        <a href="#reserver" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:w-auto sm:text-base">
                            Prendre rendez-vous
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="mailto:contact@vivre-pleinement.fr" class="text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700">
                            <svg class="size-4 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            Une question&nbsp;?
                        </a>
                    </div>

                    {{-- Trust bar --}}
                    <ul class="text-ink-soft mt-8 flex flex-wrap items-center justify-center gap-x-5 gap-y-2.5 text-sm lg:justify-start">
                        @foreach ([
                            ['m9 12 2 2 4-4', 'Praticienne ACT certifiée'],
                            ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4', 'Validée scientifiquement'],
                            ['M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z', 'Suivi 100 % personnalisé'],
                        ] as [$path, $label])
                            <li class="inline-flex items-center gap-2">
                                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}"/></svg>
                                </span>
                                <span class="font-medium">{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Colonne visuelle : modalités --}}
                <div class="lg:col-span-6">
                    <div class="mx-auto max-w-md rounded-4xl bg-white/70 p-6 shadow-lg ring-1 shadow-teal-900/5 ring-white/60 backdrop-blur-xs sm:p-7">
                        <p class="text-ink-muted text-center text-xs font-medium tracking-wider uppercase lg:text-left">Au choix, selon ce qui vous rassure</p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                [
                                    'img' => 'consultation-telephone',
                                    'alt' => 'Laura Baechlé en consultation par téléphone',
                                    'icon' => 'M5 4h4l2 5-3 2a11 11 0 0 0 5 5l2-3 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z',
                                    'label' => 'Par téléphone',
                                ],
                                [
                                    'img' => 'consultation-visio',
                                    'alt' => 'Laura Baechlé en consultation par visioconférence',
                                    'icon' => 'M2 6h14v12H2zM16 10l6-3v10l-6-3z',
                                    'label' => 'En visio',
                                ],
                            ] as $mode)
                                <div class="bg-cream-50 ring-ink/5 flex flex-col items-center gap-3 rounded-3xl p-5 text-center ring-1">
                                    <img
                                        src="{{ asset('images/'.$mode['img'].'-400.webp') }}"
                                        srcset="{{ asset('images/'.$mode['img'].'-400.webp') }} 400w, {{ asset('images/'.$mode['img'].'-800.webp') }} 800w"
                                        sizes="80px" width="80" height="80"
                                        alt="{{ $mode['alt'] }}"
                                        class="size-20 shrink-0 rounded-full object-cover object-top shadow-md ring-2 ring-white"
                                        loading="eager" decoding="async">
                                    <span class="text-ink inline-flex items-center gap-2 text-sm font-medium">
                                        <svg class="size-4 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $mode['icon'] }}"/></svg>
                                        {{ $mode['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-ink-soft mt-5 flex items-center justify-center gap-2 text-center text-sm">
                            <svg class="size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0zM12 7v5l3 2"/></svg>
                            <span><span class="text-ink font-medium">60 min</span> · à distance, où que vous soyez</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Mon histoire / empathie --}}
    <x-section bg="bg-cream-50" eyebrow="Je vous comprends" title="Votre anxiété vous gâche l'existence." headerWidth="max-w-4xl">
        <div class="text-ink-soft mx-auto max-w-4xl space-y-5 text-base leading-relaxed sm:text-lg">
            <p>
                Elle est importante et vous fait souffrir tous les jours. Vous ne comprenez pas pourquoi vous
                angoissez systématiquement devant des situations où les autres restent parfaitement sereins.
                Vous vous sentez faible, anormal·e&nbsp;; alors vous cachez vos troubles, persuadé·e que les
                autres vous prendraient pour quelqu'un de bizarre ou en quête d'attention. Vous avez peur
                d'être rejeté·e, abandonné·e.
            </p>
            <p>
                Je sais&nbsp;: c'est douloureux. Au fond, vous rêvez de vous libérer et de vivre comme tout le
                monde. Restez avec moi jusqu'au bout, car je peux vous aider à atteindre cet objectif.
            </p>
            <p>
                Je vous comprends totalement, parce que nous avons beaucoup en commun. J'ai moi aussi souffert
                de multiples troubles anxieux qui ont profondément impacté mon quotidien, et j'ai réussi à
                m'en sortir. <strong class="text-ink font-medium">Si j'ai réussi, vous le pouvez aussi.</strong>
            </p>
            <p>
                Comment&nbsp;? Grâce à la découverte de l'ACT (thérapie d'acceptation et d'engagement), un véritable
                tournant dans mon parcours. Devenue praticienne ACT, c'est avec beaucoup de sens et de conviction
                que je transmets aujourd'hui cette approche qui a littéralement changé mon rapport à l'anxiété.
            </p>
            <p>
                Ce n'est pas de votre faute si vous vous sentez si mal&nbsp;: vous n'avez simplement pas encore eu
                les bons outils. Si vous êtes sur cette page, c'est que quelque chose a changé en vous. Il existe
                deux types de personnes&nbsp;: celles qui se plaignent sans agir, et celles qui prennent leur vie en
                main. <strong class="text-ink font-medium">Aujourd'hui, vous rejoignez celles qui passent à l'action.</strong>
            </p>
        </div>
        <div class="mt-10 text-center">
            <a href="#reserver" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                <span class="border-b border-teal-700/30">Je suis motivé·e, je prends rendez-vous</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </x-section>

    {{-- Pour qui --}}
    <x-section bg="bg-white" eyebrow="Pour qui ?" title="À qui s'adresse cet accompagnement en ACT ?"
        lead="L'ACT est une approche thérapeutique validée scientifiquement. Elle fait partie des TCC de 3ᵉ vague et vise à réduire l'emprise des mécanismes qui entretiennent la souffrance psychologique."
        headerWidth="max-w-4xl">
        <div>
            <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Trouble anxieux généralisé (TAG)',
                    'Crises d\'angoisse, anxiété matinale, angoisse nocturne',
                    'Phobie spécifique',
                    'Agoraphobie, phobie sociale',
                    'Addictions',
                    'TOC, phobie d\'impulsion',
                    'Hypocondrie',
                    'Manque de confiance et d\'estime de soi',
                    'Dépersonnalisation, déréalisation',
                    'Stress et performance au travail',
                    'Troubles mixtes (anxio-dépressif)',
                    'Fatigue mentale, burnout',
                ] as $domain)
                    <li class="bg-cream-50 ring-cream-200 flex items-start gap-3 rounded-2xl px-4 py-3 ring-1">
                        <svg class="mt-0.5 size-5 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                        <span class="text-ink-soft text-sm sm:text-base">{{ $domain }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-section>

    {{-- Témoignages : preuve sociale avant de présenter la réservation --}}
    <x-section bg="bg-cream-50" eyebrow="Témoignages" title="Elles en parlent mieux que moi."
        lead="Quelques retours de personnes que j'ai accompagnées."
        headerWidth="max-w-4xl">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ([
                [
                    'text' => "Quelle chance d'avoir trouvé quelqu'un qui comprenne pleinement mon anxiété et mes angoisses, puisqu'elle a vécu les mêmes. Quelle chance de se dire qu'on peut en sortir quand on voit le sourire radieux de Laura. Merci pour l'échange, les conseils, et pour l'aide que vous apportez aux personnes en souffrance.",
                    'author' => 'Angéline',
                    'context' => 'Anxiété et angoisses',
                ],
                [
                    'text' => "J'ai vécu très longtemps dans une souffrance émotionnelle. Dès nos premiers échanges, j'ai pris conscience de la nécessité de prendre la responsabilité de cette souffrance. Laura m'a écoutée, conseillée. À toutes les personnes qui cherchent l'apaisement : persévérez, une écoute bienveillante favorise la prise de conscience.",
                    'author' => 'Jocelyne',
                    'context' => 'Souffrance émotionnelle',
                ],
                [
                    'text' => "Notre appel m'a fait le plus grand bien à un moment où mes émotions étaient en ébullition. Laura est à l'écoute, compréhensive, et c'est agréable de parler avec une personne qui sait de quoi elle parle. Si vous vous sentez incompris·e par certains psychologues, n'hésitez pas à vous tourner vers elle.",
                    'author' => 'Olivia',
                    'context' => 'Gestion émotionnelle',
                ],
            ] as $t)
                <figure class="ring-ink/5 flex flex-col rounded-3xl bg-white p-7 shadow-xs ring-1">
                    <svg class="size-8 text-teal-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M9.5 4.5C6 4.5 3 7.5 3 11v9h6v-9H6c0-2 1.5-4 3.5-4v-2.5zm9 0c-3.5 0-6.5 3-6.5 6.5v9h6v-9h-3c0-2 1.5-4 3.5-4v-2.5z"/>
                    </svg>
                    <blockquote class="text-ink-soft mt-4 flex-1 text-sm leading-relaxed">
                        {{ $t['text'] }}
                    </blockquote>
                    <figcaption class="border-ink/10 mt-6 border-t pt-4">
                        <p class="text-ink text-sm font-medium">{{ $t['author'] }}</p>
                        <p class="text-ink-muted text-xs">{{ $t['context'] }}</p>
                    </figcaption>
                </figure>
            @endforeach
        </div>

        <p class="text-ink-muted mt-8 text-center text-xs">
            Témoignages authentiques, publiés avec l'accord des personnes concernées.
        </p>
    </x-section>

    {{-- Déroulé de la séance : lève la dernière objection juste avant la réservation --}}
    <x-section bg="bg-white" eyebrow="Le déroulé" title="Comment se passe une séance d'ACT ?" headerWidth="max-w-4xl">
        <div class="text-ink-soft mx-auto max-w-4xl space-y-5 text-base leading-relaxed sm:text-lg">
            <p>
                Une fois la date, l'heure et la formule choisies (téléphone ou visio), je vous contacte
                directement au moment indiqué.
            </p>
            <p>
                Lors de la première séance, je vous pose différentes questions pour mieux comprendre votre
                difficulté et la manière dont elle s'inscrit dans votre quotidien&nbsp;: c'est ce qu'on appelle en
                ACT une <em>analyse fonctionnelle</em>.
            </p>
            <p>
                Dans cette approche, on considère que certaines difficultés sont maintenues par des modes de
                fonctionnement rigides, qui empêchent la flexibilité psychologique. L'objectif de cette première
                séance est donc de comprendre votre situation, d'identifier ces fonctionnements, puis de définir
                ensemble des pistes d'accompagnement adaptées à vos besoins.
            </p>
        </div>
    </x-section>

    {{-- Offre + calendrier de réservation : l'action finale --}}
    <x-section id="reserver" bg="bg-cream-50" eyebrow="Réservation" title="Et si vous preniez rendez-vous maintenant ?"
        lead="Choisissez votre créneau ci-dessous, je vous contacte au moment indiqué."
        headerWidth="max-w-4xl">
        @if (! $primaryService)
            <p class="text-ink-soft ring-ink/5 mx-auto max-w-xl rounded-3xl bg-white p-8 text-center ring-1">
                Aucun créneau n'est disponible à la réservation pour le moment.
                <a href="mailto:contact@vivre-pleinement.fr" class="font-medium text-teal-700 hover:text-teal-800">Écrivez-moi</a> directement.
            </p>
        @else
            <div class="mx-auto max-w-3xl">
                <div class="flex flex-wrap items-baseline justify-center gap-x-4 gap-y-1 text-center">
                    <h3 class="text-ink font-serif text-2xl font-medium">{{ $primaryService->name }}</h3>
                    <p class="text-ink-soft">
                        <span class="text-ink font-serif text-2xl font-medium">{{ $primaryService->isFree() ? 'Gratuit' : number_format($primaryService->price, 0, ',', ' ').' €' }}</span>
                        · {{ $primaryService->duration_minutes }} min · par téléphone ou en visio
                    </p>
                </div>
            </div>

            <div class="mt-10">
                @livewire('booking-calendar', ['service' => $primaryService])
            </div>
        @endif
    </x-section>

    {{-- CTA flottant mobile : se révèle au scroll, se masque sur le calendrier --}}
    @if ($primaryService)
        <div
            data-booking-cta
            class="pointer-events-none fixed inset-x-0 bottom-0 z-40 translate-y-24 px-4 pb-[max(1rem,env(safe-area-inset-bottom))] opacity-0 transition duration-300 ease-out lg:hidden"
        >
            <a
                href="#reserver"
                class="flex items-center justify-center gap-2 rounded-full bg-teal-700 px-6 py-3.5 text-center text-sm font-medium text-white shadow-lg shadow-teal-900/30 transition hover:bg-teal-800"
            >
                Prendre rendez-vous
                <span aria-hidden="true">→</span>
            </a>
        </div>
    @endif
    </main>

    <x-section
        eyebrow="Avant de réserver"
        title="Questions fréquentes."
        bg="bg-white"
    >
        <div class="mx-auto max-w-3xl space-y-4">
            @foreach (\App\Support\BookingFaq::all() as $item)
                <x-accordion-item :question="$item['q']" :open="$loop->first">
                    {{ $item['a'] }}
                </x-accordion-item>
            @endforeach
        </div>
    </x-section>

    @include('home.sections.footer')
@endsection
