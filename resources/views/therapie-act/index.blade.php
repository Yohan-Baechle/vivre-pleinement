@extends('layouts.site')

@php
    $faq = [
        [
            'q' => "La thérapie ACT est-elle efficace contre l'anxiété ?",
            'a' => "Oui. L'ACT fait partie des thérapies cognitives et comportementales de 3ᵉ vague et son efficacité sur les troubles anxieux est appuyée par plusieurs centaines d'essais cliniques. Plutôt que de chercher à supprimer l'anxiété, elle réduit l'emprise des pensées et sensations anxieuses sur vos décisions, ce qui diminue durablement la souffrance.",
        ],
        [
            'q' => 'Combien de séances de thérapie ACT faut-il ?',
            'a' => "Cela dépend de votre situation. Beaucoup de personnes ressentent un premier changement de perspective en quelques séances, car l'ACT donne rapidement des outils concrets à pratiquer entre les rendez-vous. Un accompagnement dure généralement entre 6 et 15 séances.",
        ],
        [
            'q' => 'Quelle est la différence entre ACT et TCC classique ?',
            'a' => "La TCC classique cherche surtout à identifier et modifier le contenu des pensées (restructuration cognitive). L'ACT, elle, travaille sur votre relation aux pensées : apprendre à les observer sans lutter contre elles, pour libérer votre énergie vers ce qui compte vraiment pour vous. Les deux approches appartiennent à la même famille et sont complémentaires.",
        ],
        [
            'q' => 'Peut-on suivre une thérapie ACT à distance ?',
            'a' => "Oui, l'ACT se prête très bien au format à distance. J'accompagne mes clients par téléphone ou en visioconférence, partout en France. Les exercices (défusion, pleine conscience, clarification des valeurs) se pratiquent exactement de la même façon qu'en cabinet.",
        ],
        [
            'q' => "Une praticienne ACT est-elle une psychologue ?",
            'a' => "Pas nécessairement. Je suis praticienne ACT formée à cette approche, et je l'ai d'abord expérimentée pour me libérer de mes propres troubles anxieux. Je ne pose pas de diagnostic et ne remplace pas un médecin ou un psychologue : si votre situation le nécessite, je vous orienterai vers un professionnel de santé.",
        ],
    ];

    $faqLd = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        '@id' => route('therapie-act').'#faq',
        'url' => route('therapie-act'),
        'inLanguage' => 'fr-FR',
        'mainEntity' => collect($faq)->map(fn ($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->all(),
    ];

    $processes = [
        ['title' => "L'acceptation", 'text' => "Faire de la place aux émotions et sensations désagréables au lieu de les fuir ou de les combattre, car c'est la lutte qui entretient l'anxiété."],
        ['title' => 'La défusion cognitive', 'text' => "Prendre de la distance avec vos pensées (« j'ai la pensée que… ») pour qu'elles cessent de dicter vos actions."],
        ['title' => "Le contact avec l'instant présent", 'text' => 'Revenir à ce qui se passe ici et maintenant, plutôt que de ruminer le passé ou d\'anticiper le pire.'],
        ['title' => 'Le soi-observateur', 'text' => "Découvrir que vous n'êtes pas vos pensées : vous êtes celui ou celle qui les observe."],
        ['title' => 'Les valeurs', 'text' => 'Clarifier ce qui compte vraiment pour vous : ce sont vos valeurs, pas votre anxiété, qui doivent guider votre vie.'],
        ['title' => "L'action engagée", 'text' => 'Poser des actes concrets, alignés avec vos valeurs, même en présence d\'inconfort.'],
    ];
@endphp

@section('title', 'Thérapie ACT : définition, principes et efficacité | Laura Baechlé')
@section('description', "Qu'est-ce que la thérapie ACT (acceptation et engagement) ? Définition, les 6 processus, efficacité sur les troubles anxieux et déroulement d'une séance, par une praticienne qui l'a vécue.")
@section('canonical', route('therapie-act'))
@section('og_title', "La thérapie ACT expliquée simplement")
@section('og_description', "Définition, principes et efficacité de la thérapie d'acceptation et d'engagement (ACT) sur les troubles anxieux.")

@push('head')
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'La thérapie ACT'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    Comprendre l'approche
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                    La thérapie ACT, expliquée simplement
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Définition, principes, efficacité et déroulement d'une séance, par une praticienne
                    qui a d'abord utilisé l'ACT pour se libérer de ses propres troubles anxieux.
                </p>
            </div>
        </div>
    </header>

    <main id="main">

    <x-section bg="bg-cream-50" title="Qu'est-ce que la thérapie ACT ?" headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-base leading-relaxed sm:text-lg">
            <p>
                La <strong class="text-ink">thérapie d'acceptation et d'engagement</strong> (ACT, prononcée
                « acte ») est une psychothérapie qui appartient aux thérapies cognitives et comportementales
                de 3ᵉ vague. Contrairement aux approches qui cherchent à éliminer les pensées et émotions
                désagréables, l'ACT apprend à <strong class="text-ink">changer votre relation avec elles</strong> :
                les accueillir sans lutter, prendre de la distance avec le mental, puis engager votre énergie
                dans des actions alignées avec vos valeurs. Développée par le psychologue Steven C. Hayes dans
                les années 1980, elle vise la <strong class="text-ink">flexibilité psychologique</strong> : la
                capacité à agir selon ce qui compte pour vous, même en présence d'anxiété. C'est cette
                flexibilité, et non l'absence d'émotions difficiles, qui permet de retrouver une vie riche et
                pleine de sens.
            </p>
            <p>
                Dans les troubles anxieux, ce renversement change tout : la lutte contre l'anxiété
                (évitements, réassurance, contrôle des pensées) est précisément ce qui l'entretient.
                L'ACT désamorce ce cercle vicieux.
            </p>
        </div>
    </x-section>

    <x-section bg="bg-white" title="Comment fonctionne l'ACT ?"
        lead="L'ACT développe la flexibilité psychologique à travers six processus complémentaires, souvent représentés en hexagone (l'« hexaflex »)." headerWidth="max-w-3xl">
        <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($processes as $process)
                <div class="ring-ink/5 rounded-3xl bg-cream-50 p-7 ring-1">
                    <span class="flex size-8 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                        {{ $loop->iteration }}
                    </span>
                    <h3 class="text-ink mt-4 font-serif text-lg font-medium">{{ $process['title'] }}</h3>
                    <p class="text-ink-soft mt-2 text-sm leading-relaxed">{{ $process['text'] }}</p>
                </div>
            @endforeach
        </div>
    </x-section>

    <x-section bg="bg-cream-50" title="L'ACT est-elle validée scientifiquement ?" headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-base leading-relaxed sm:text-lg">
            <p>
                Oui. L'efficacité de l'ACT est étudiée depuis plus de trente ans et appuyée par
                <strong class="text-ink">plusieurs centaines d'essais cliniques randomisés</strong>, notamment
                sur les troubles anxieux, la dépression, les TOC et la douleur chronique. Elle est reconnue
                comme une approche à l'efficacité empiriquement démontrée, et elle est diffusée en France par
                les associations de thérapies comportementales et cognitives comme
                l'<a href="https://www.aftcc.org" target="_blank" rel="noopener noreferrer" class="font-medium text-teal-700 underline-offset-2 hover:underline">AFTCC</a>
                et au niveau international par
                l'<a href="https://contextualscience.org" target="_blank" rel="noopener noreferrer" class="font-medium text-teal-700 underline-offset-2 hover:underline">ACBS</a>
                (Association for Contextual Behavioral Science).
            </p>
            <p>
                Ce que cela signifie concrètement pour vous : l'ACT n'est ni une mode ni une méthode
                miracle : c'est une approche structurée, enseignable et mesurable, dont les outils se
                pratiquent au quotidien.
            </p>
        </div>
    </x-section>

    <x-section bg="bg-white" title="Dans quels cas l'ACT peut-elle vous aider ?" headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-base leading-relaxed sm:text-lg">
            <p>
                L'ACT est particulièrement adaptée aux troubles anxieux, là où la lutte contre les pensées
                entretient le problème :
                <a href="{{ url('/blog/trouble-anxieux-generalise') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">trouble anxieux généralisé</a>,
                <a href="{{ url('/blog/toc-troubles-obsessionnels-compulsifs') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">TOC</a>,
                <a href="{{ url('/blog/les-phobies-dimpulsion') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">phobies d'impulsion</a>,
                <a href="{{ url('/blog/phobie-sociale') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">phobie sociale</a>,
                <a href="{{ url('/blog/ruminations') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">ruminations mentales</a>
                ou encore <a href="{{ url('/blog/burn-out') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">burn-out</a>.
            </p>
            <p>
                J'ai moi-même traversé plusieurs de ces troubles avant de devenir praticienne, c'est
                l'ACT qui a marqué le tournant de mon parcours.
                <a href="{{ route('about') }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">Je raconte mon histoire ici</a>.
            </p>
        </div>
    </x-section>

    <x-section bg="bg-cream-50" title="Comment se déroule une séance avec moi ?" headerWidth="max-w-3xl">
        <div class="text-ink-soft mx-auto max-w-2xl space-y-6 text-base leading-relaxed sm:text-lg">
            <p>
                J'accompagne à distance, par téléphone ou en visioconférence. La première séance sert à
                comprendre votre situation et la manière dont vos difficultés s'inscrivent dans votre
                quotidien (l'« analyse fonctionnelle »). Ensuite, chaque séance combine échanges, exercices
                pratiques d'ACT et pistes concrètes à expérimenter entre les rendez-vous.
            </p>
            <div class="pt-2 text-center">
                <a href="{{ route('booking.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-teal-700 px-8 py-4 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                    Prendre rendez-vous
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </x-section>

    <x-section bg="bg-white" title="Vos questions sur la thérapie ACT." headerWidth="max-w-3xl">
        <div class="mx-auto max-w-3xl space-y-4">
            @foreach ($faq as $item)
                <x-accordion-item :question="$item['q']" :open="$loop->first">
                    {{ $item['a'] }}
                </x-accordion-item>
            @endforeach
        </div>
    </x-section>

    <div class="bg-cream-50 py-12 sm:py-16">
        <div class="site-container">
            <x-health-disclaimer class="mx-auto max-w-4xl" />

            <x-content-cta class="mx-auto mt-10 max-w-4xl" />
        </div>
    </div>

    </main>

    @include('home.sections.footer')
@endsection
