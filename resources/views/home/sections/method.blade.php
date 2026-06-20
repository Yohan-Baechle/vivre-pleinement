@php
    $steps = [
        [
            'num' => '01',
            'title' => 'Rendez-vous découverte',
            'desc' => 'Un premier échange gratuit de 30 minutes pour faire connaissance, comprendre votre situation et voir si je peux vous aider. Sans engagement.',
        ],
        [
            'num' => '02',
            'title' => 'Plan d\'accompagnement personnalisé',
            'desc' => 'Ensemble, on définit vos objectifs et on construit un chemin sur mesure : nombre de séances, fréquence, outils prioritaires.',
        ],
        [
            'num' => '03',
            'title' => 'Séances régulières',
            'desc' => 'Séances individuelles en visio, dans un cadre bienveillant et confidentiel. Entre les séances, des outils concrets à appliquer au quotidien.',
        ],
    ];
@endphp

<x-section
    id="methode"
    eyebrow="Comment ça se passe"
    title="Un chemin clair, en trois étapes."
    lead="Pas de surprise, pas de pression. Vous savez à quoi vous attendre dès le premier contact."
>
    <ol class="grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-8">
        @foreach ($steps as $step)
            <li class="ring-ink/5 relative rounded-3xl bg-white p-7 shadow-xs ring-1">
                <span class="font-serif text-5xl font-medium text-teal-700/85" aria-hidden="true">{{ $step['num'] }}</span>
                <h3 class="text-ink mt-3 font-serif text-xl font-medium">{{ $step['title'] }}</h3>
                <p class="text-ink-soft mt-3 text-sm leading-relaxed">{{ $step['desc'] }}</p>
            </li>
        @endforeach
    </ol>

    <div class="mt-12 text-center">
        <a href="{{ route('booking.index') }}" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
            Réserver mon RDV découverte
            <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
        </a>
    </div>
</x-section>
