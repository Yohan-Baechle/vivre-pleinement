@php
    $troubles = [
        [
            'title' => 'Anxiété généralisée (TAG)',
            'desc' => 'Une inquiétude constante, des pensées qui tournent, une fatigue mentale qui ne lâche jamais. Apprenons à apaiser ce flot intérieur.',
            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        ],
        [
            'title' => 'Phobies',
            'desc' => 'Phobie sociale, agoraphobie, peur de conduire, claustrophobie... Identifions ensemble les déclencheurs et reprenons le contrôle.',
            'icon' => 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        ],
        [
            'title' => 'TOC',
            'desc' => 'Pensées intrusives, rituels qui prennent du temps, vérifications répétées. Une approche douce pour s\'en libérer progressivement.',
            'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
        ],
        [
            'title' => 'Attaques de panique',
            'desc' => 'Ces crises soudaines, terrifiantes, qui donnent l\'impression de mourir. Comprendre leur mécanisme pour désamorcer la peur de la peur.',
            'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
        ],
        [
            'title' => 'Burnout',
            'desc' => 'Épuisement professionnel ou parental, perte de sens, sentiment d\'être vidé. Reconstruire pas à pas, sans pression.',
            'icon' => 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z',
        ],
        [
            'title' => 'Phobie scolaire & anxiété de l\'enfant',
            'desc' => 'Refus scolaire, somatisations, peurs envahissantes. Accompagnement bienveillant des enfants et adolescents.',
            'icon' => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
        ],
    ];
@endphp

<x-section
    id="troubles"
    eyebrow="Troubles accompagnés"
    title="Vous reconnaissez-vous ?"
    lead="L'anxiété prend des formes très différentes. Quelle que soit la vôtre, vous n'êtes pas seule à la vivre, et des solutions existent."
>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($troubles as $trouble)
            <article class="group ring-ink/5 relative rounded-3xl bg-white p-6 shadow-xs ring-1 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-teal-700/10 hover:ring-teal-200">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 ring-1 ring-teal-100">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trouble['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-ink mt-5 font-serif text-xl font-medium">{{ $trouble['title'] }}</h3>
                <p class="text-ink-soft mt-2 text-sm leading-relaxed">{{ $trouble['desc'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="ring-ink/5 mt-10 rounded-3xl bg-white/60 p-6 text-center ring-1 sm:p-8">
        <p class="text-ink-soft text-sm leading-relaxed">
            J'accompagne aussi les <strong class="text-ink font-medium">crises d'angoisse</strong>, l'anxiété matinale et l'angoisse nocturne, l'<strong class="text-ink font-medium">agoraphobie</strong> et la phobie sociale, l'hypocondrie, la phobie d'impulsion, la dépersonnalisation/déréalisation, le manque de confiance et d'estime de soi, les addictions, le stress lié au travail ainsi que le trouble anxio-dépressif.
        </p>
        <p class="text-ink mt-3 text-sm font-medium">
            Vous ne vous retrouvez dans aucune de ces cases&nbsp;? Parlons-en, chaque parcours est unique.
        </p>
    </div>
</x-section>
