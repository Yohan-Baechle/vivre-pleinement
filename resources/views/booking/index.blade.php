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

    {{-- ════════ HERO : point de départ du parcours ════════ --}}
    <header data-booking-hero class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-16 sm:pt-36 sm:pb-24">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Prendre rendez-vous'],
            ]" />

            <div class="mx-auto mt-10 max-w-4xl text-center lg:mt-14">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Accompagnement en thérapie d'acceptation et d'engagement (ACT)
                </p>
                <h1 class="text-ink mt-6 font-serif text-3xl/tight font-medium tracking-tight sm:text-4xl/tight lg:text-5xl/tight">
                    Oui, vous pouvez vous libérer de vos troubles anxieux&nbsp;: laissez-moi vous accompagner sur le chemin de la guérison&nbsp;!
                </h1>
                <p class="text-ink font-serif mt-5 text-xl font-medium sm:text-2xl">
                    Par téléphone ou en visio
                </p>
                <p class="text-ink-soft mx-auto mt-5 max-w-2xl text-base sm:text-lg">
                    Afin d'obtenir un accompagnement individuel, personnalisé et unique pour vous libérer de
                    vos troubles anxieux
                </p>

                {{-- Modalités : avatars --}}
                <div class="mt-10 flex items-center justify-center gap-8 sm:gap-12">
                    @foreach ([
                        ['img' => 'consultation-visio', 'alt' => 'Laura en visio', 'label' => 'En visio'],
                        ['img' => 'consultation-telephone', 'alt' => 'Laura au téléphone', 'label' => 'Par téléphone'],
                    ] as $mode)
                        <figure class="flex flex-col items-center gap-3">
                            <img
                                src="{{ asset('images/'.$mode['img'].'-400.webp') }}"
                                srcset="{{ asset('images/'.$mode['img'].'-400.webp') }} 400w, {{ asset('images/'.$mode['img'].'-800.webp') }} 800w"
                                sizes="(min-width: 640px) 144px, 112px" width="144" height="144"
                                alt="{{ $mode['alt'] }}"
                                class="size-28 shrink-0 rounded-full object-cover object-top shadow-lg ring-4 ring-white sm:size-36"
                                loading="eager" decoding="async">
                            <figcaption class="text-ink text-sm font-medium sm:text-base">{{ $mode['label'] }}</figcaption>
                        </figure>
                    @endforeach
                </div>

                {{-- Accès direct à l'agenda : aperçu des prochaines disponibilités --}}
                @if ($primaryService && $upcomingSlots->isNotEmpty())
                    <div class="ring-ink/5 mx-auto mt-10 max-w-md rounded-3xl bg-white/80 p-5 shadow-lg shadow-teal-900/5 ring-1 backdrop-blur-xs sm:p-6">
                        <p class="text-ink-muted flex items-center justify-center gap-2 text-xs font-medium tracking-wider uppercase">
                            <svg class="size-4 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            Prochaines disponibilités
                        </p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2.5">
                            @foreach ($upcomingSlots as $slot)
                                <a href="#reserver"
                                    class="ring-teal-200 inline-flex flex-col items-center rounded-2xl bg-teal-50 px-4 py-2.5 text-center ring-1 transition hover:bg-teal-100">
                                    <span class="text-ink text-sm font-medium capitalize">{{ $slot['start']->locale('fr')->isoFormat('ddd D MMM') }}</span>
                                    <span class="text-teal-700 text-sm font-semibold">{{ $slot['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="#reserver" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-8 py-3.5 text-sm font-medium text-white shadow-md shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                            Voir tout l'agenda &amp; réserver
                            <span aria-hidden="true">↓</span>
                        </a>
                    </div>
                @else
                    <div class="mt-10">
                        <a href="#reserver" class="inline-flex items-center justify-center gap-2 rounded-full bg-teal-700 px-8 py-4 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                            Prendre rendez-vous
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                @endif

                <p class="text-ink-muted mt-6 text-sm">
                    <strong class="text-teal-700">Une question&nbsp;?</strong>
                    Contactez-moi&nbsp;: <a href="mailto:contact@vivre-pleinement.fr" class="font-medium text-teal-700 underline-offset-2 hover:underline">contact@vivre-pleinement.fr</a>
                </p>
            </div>
        </div>
    </header>

    {{-- Mon histoire / empathie --}}
    <x-section bg="bg-cream-50" eyebrow="Je vous comprends" title="Votre anxiété vous gâche l'existence." headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-lg leading-relaxed sm:text-xl">
            <p class="first-letter:float-left first-letter:mr-3 first-letter:font-serif first-letter:text-6xl first-letter:leading-[0.8] first-letter:font-medium first-letter:text-teal-700">
                Elle est importante et vous fait souffrir tous les jours. Vous ne comprenez pas pourquoi vous
                angoissez systématiquement devant des choses où les autres restent totalement zen. Vous vous
                sentez faible, anormal. D'ailleurs, vous cachez au maximum vos troubles anxieux, car vous êtes
                persuadé que les autres vous prendraient pour un faible, un individu très bizarre. Une
                personne qui fait exprès d'être comme cela pour attirer l'attention. Vous avez peur que les
                autres vous rejettent, vous abandonnent.
            </p>
            <p>
                Je sais, c'est douloureux. En fait, vous rêvez de vous libérer de vos troubles anxieux et d'être
                une personne comme tout le monde. Restez avec moi jusqu'au bout, car je peux vous aider à
                atteindre ces objectifs.
            </p>
            <p>
                En effet, je vous comprends totalement, car nous avons beaucoup de choses en commun. J'ai
                également souffert de multiples troubles anxieux qui ont eu un impact important sur ma vie
                quotidienne et j'ai réussi à m'en sortir. <strong class="text-ink font-medium">Et si j'ai réussi, vous pouvez réussir aussi&nbsp;!</strong>
            </p>
            <p>
                Et comment y suis-je parvenue&nbsp;? Grâce à la découverte de l'ACT (thérapie d'acceptation et
                d'engagement), laquelle a marqué un véritable tournant dans mon parcours.
            </p>
            <p>
                Aujourd'hui, étant devenue praticienne ACT, c'est avec beaucoup de sens et de conviction que je
                souhaite transmettre cette approche à mon tour puisque celle-ci a littéralement changé mes
                troubles anxieux.
            </p>
        </div>

        {{-- Mise en exergue : le passage clé --}}
        <figure class="mx-auto mt-12 max-w-3xl border-l-2 border-teal-300 pl-6 sm:pl-8">
            <blockquote class="text-ink font-serif text-xl leading-snug font-medium sm:text-2xl">
                <p class="mb-4">
                    En fait, ce n'est pas de votre faute si vous vous sentez si mal. Vous n'avez simplement pas eu
                    les bons outils jusqu'à maintenant.
                </p>
                <p>
                    Écoutez&nbsp;: si vous souhaitez aller mieux, vous devez agir. Mais si vous vous trouvez sur cette
                    page, c'est que quelque chose a changé en vous. En effet, il existe deux types de personnes&nbsp;: les
                    victimes, qui se plaignent constamment sans agir, et les autres, qui agissent et prennent leur
                    vie en main. Et vous rejoignez, désormais, les personnes qui passent à l'action.
                </p>
            </blockquote>
        </figure>

        <div class="mt-10 text-center">
            <a href="#reserver" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                <span class="border-b border-teal-700/30">Si vous êtes motivé, vous pouvez prendre rendez-vous avec moi.</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </x-section>

    {{-- Pour qui --}}
    <x-section bg="bg-white" eyebrow="Pour qui ?" title="Pour qui est fait cet accompagnement en ACT ?" headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-base leading-relaxed sm:text-lg">
            <p>
                Admettez-le&nbsp;: vous souhaitez des résultats rapides. Et c'est normal, puisque vous ne supportez
                plus de vivre avec toute cette anxiété. C'est ici que j'interviens en vous proposant mes services
                d'accompagnement.
            </p>
            <p>
                L'ACT est une approche thérapeutique dont l'efficacité est validée scientifiquement. Elle fait
                partie des TCC de 3ᵉ vague, qui enrichissent les thérapies cognitives et comportementales
                classiques.
            </p>
            <p>
                L'objectif&nbsp;? Réduire l'impact des mécanismes et apprentissages qui entretiennent la souffrance
                psychologique.
            </p>
            <p class="text-ink font-medium">Pour ma part, je peux vous accompagner dans les domaines suivants&nbsp;:</p>
        </div>

        <ul class="mx-auto mt-10 flex max-w-4xl flex-wrap justify-center gap-2.5 sm:gap-3">
            @foreach ([
                'Trouble anxieux généralisé (TAG)',
                'Crises d\'angoisse, anxiété matinale, angoisse nocturne, etc.',
                'Phobie spécifique',
                'Agoraphobie / phobie sociale',
                'Addictions',
                'TOC / phobie d\'impulsion',
                'Hypocondrie',
                'Manque de confiance et d\'estime de soi',
                'Dépersonnalisation / déréalisation',
                'Stress et difficultés liées à la performance au travail',
                'Troubles mixtes d\'anxiété (trouble anxio-dépressif)',
                'Fatigue mentale, burnout',
            ] as $domain)
                <li class="bg-cream-50 ring-cream-200 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm ring-1">
                    <svg class="size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 12 2 2 4-4"/></svg>
                    <span class="text-ink-soft">{{ $domain }}</span>
                </li>
            @endforeach
        </ul>
    </x-section>

    {{-- Le déroulé --}}
    <x-section bg="bg-cream-50" eyebrow="Le déroulé" title="Comment se déroule la séance d'ACT ?" headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-base leading-relaxed sm:text-lg">
            <p>
                Lorsque vous aurez choisi la date, l'heure du rendez-vous et la formule (visio ou téléphone), je
                vous contacterai directement à la date et à l'heure indiquées.
            </p>
            <p>
                Lors de la première séance, je vous poserai différentes questions afin de mieux comprendre votre
                difficulté et la manière dont elle s'inscrit dans votre quotidien. Cette étape correspond à ce que
                l'on appelle en ACT une <em>analyse fonctionnelle</em>.
            </p>
            <p>
                Dans une approche ACT, on considère que certaines difficultés peuvent être maintenues par des
                modes de fonctionnement rigides, qui empêchent la flexibilité psychologique.
            </p>
            <p>
                L'objectif de cette première séance est donc de mieux comprendre votre situation, d'identifier ces
                fonctionnements, puis de définir ensemble des pistes d'accompagnement adaptées à vos besoins.
            </p>
        </div>
    </x-section>

    {{-- Témoignages --}}
    <x-section bg="bg-white" eyebrow="Témoignages" title="Elles en parlent mieux que moi."
        lead="Quelques retours de personnes que j'ai accompagnées." headerWidth="max-w-3xl">
        <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ([
                [
                    'text' => "Je remercie Laura pour sa bienveillance, sa gentillesse et son écoute. Quelle « chance » d'avoir trouvé quelqu'un qui comprenne pleinement mon anxiété et mes angoisses puisqu'elle à vécu les mêmes. Quelle chance de se dire qu'on peut en sortir quand on voit le sourire radieux de Laura. Merci pour l'échange, les conseils et les vidéos que je ne manquerai pas de suivre. Encore merci Laura d'aider les personnes en souffrance, car oui c'est une véritable souffrance que de passer par cela. Une fracture est douloureuse mais rien n'est comparable à cette anxiété envahissante qui pourrit l'existence.",
                    'author' => 'Angéline',
                    'context' => 'Anxiété et angoisses',
                ],
                [
                    'text' => "Bonjour à tous. J'ai vécu pendant très longtemps dans une souffrance émotionnelle. J'ai fini par prendre rdv avec un thérapeute (Laura). Dès nos premiers échanges, j'ai pris conscience de la nécessité pour moi de prendre la responsabilité de cette souffrance. Laura m'a écoutée, conseillée. Je lui remercie pour l'accueil qu'elle m'a réservé. J'ai envie de dire à toutes les personnes qui cherchent l'apaisement qu'il faut persévérer. Il est difficile de prendre conscience par soi-même. Une écoute bienveillante de la part d'un professionnel favorise la prise de conscience. Je vous souhaite à tous beaucoup de courage, de persévérance dans votre quête.",
                    'author' => 'Jocelyne',
                    'context' => 'Souffrance émotionnelle',
                ],
                [
                    'text' => "Je remercie Laura pour son écoute et ses conseils lors de notre appel qui m'a fait le plus grand bien à un moment où mes émotions étaient en ébullition. Laura est à l'écoute, compréhensive et surtout, c'est agréable de parler avec une personne qui sait de quoi elle parle (et de quoi on lui parle). Je pense que l'expérience de vie est parfois aussi (voire plus) efficace qu'un master en psychologie. Si vous vous sentez incompris par certains psychologues (ou qu'ils vous donnent des solutions inefficaces) n'hésitez pas à vous tourner vers Laura.",
                    'author' => 'Olivia',
                    'context' => 'Gestion émotionnelle',
                ],
            ] as $t)
                <figure class="ring-ink/5 flex flex-col rounded-3xl bg-cream-50 p-7 shadow-xs ring-1">
                    <svg class="size-8 text-teal-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M9.5 4.5C6 4.5 3 7.5 3 11v9h6v-9H6c0-2 1.5-4 3.5-4v-2.5zm9 0c-3.5 0-6.5 3-6.5 6.5v9h6v-9h-3c0-2 1.5-4 3.5-4v-2.5z"/>
                    </svg>
                    <blockquote class="text-ink-soft mt-4 flex-1 text-sm leading-relaxed">
                        {{ $t['text'] }}
                    </blockquote>
                    <figcaption class="border-ink/10 mt-6 border-t pt-4">
                        <p class="text-ink font-serif text-base font-medium">{{ $t['author'] }}</p>
                        <p class="text-ink-muted text-xs">{{ $t['context'] }}</p>
                    </figcaption>
                </figure>
            @endforeach
        </div>

        <p class="text-ink-muted mt-8 text-center text-xs">
            Témoignages authentiques, publiés avec l'accord des personnes concernées.
        </p>
    </x-section>

    {{-- Réservation --}}
    <x-section id="reserver" bg="bg-cream-50" eyebrow="Réservation" title="Et si vous preniez rendez-vous maintenant ?"
        lead="Choisissez votre créneau ci-dessous, je vous contacte au moment indiqué." headerWidth="max-w-3xl">
        @if (! $primaryService)
            <p class="text-ink-soft ring-ink/5 mx-auto max-w-xl rounded-3xl bg-white p-8 text-center ring-1">
                Aucun créneau n'est disponible à la réservation pour le moment.
                <a href="mailto:contact@vivre-pleinement.fr" class="font-medium text-teal-700 hover:text-teal-800">Écrivez-moi</a> directement.
            </p>
        @else
            <div class="mx-auto max-w-3xl">
                {{-- Bandeau offre : pas de carte, juste un liseré pour éviter le double encadré --}}
                <div class="border-cream-300 flex flex-wrap items-baseline justify-center gap-x-4 gap-y-1 border-b pb-6 text-center">
                    <h3 class="text-ink font-serif text-2xl font-medium">{{ $primaryService->name }}</h3>
                    <p class="text-ink-soft">
                        <span class="text-ink font-serif text-2xl font-medium">{{ $primaryService->isFree() ? 'Gratuit' : number_format($primaryService->price, 0, ',', ' ').' €' }}</span>
                        · {{ $primaryService->duration_minutes }} min · par téléphone ou en visio
                    </p>
                </div>
                <div class="mt-8">
                    @livewire('booking-calendar', ['service' => $primaryService])
                </div>
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

    {{-- ════════ FAQ ════════ --}}
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
