@php
    $steps = [
        [
            'num' => '01',
            'title' => 'Premier rendez-vous',
            'desc' => 'Une séance individuelle d\'une heure, en visio ou par téléphone, pour faire connaissance, comprendre votre situation et poser vos objectifs.',
        ],
        [
            'num' => '02',
            'title' => 'Plan d\'accompagnement personnalisé',
            'desc' => 'Ensemble, nous définissons vos objectifs et nous construisons un chemin sur mesure : nombre de séances, fréquence, outils prioritaires.',
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
    lead="Pas de surprise, pas de pression. Vous savez à quoi vous attendre dès le premier rendez-vous."
>
    <ol class="mx-auto max-w-2xl space-y-0">
        @foreach ($steps as $step)
            <li
                class="timeline-step group relative flex gap-6 pb-10 last:pb-0"
                style="--reveal-delay: {{ $loop->index * 180 }}ms"
            >
                @unless ($loop->last)
                    <span class="timeline-line absolute top-14 left-6 -ml-px h-[calc(100%-2rem)] w-0.5 rounded-full bg-linear-to-b from-teal-400 via-teal-300 to-teal-100" aria-hidden="true"></span>
                @endunless
                <span class="timeline-dot relative z-10 flex size-12 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-teal-600 to-teal-800 font-serif text-lg font-medium text-white shadow-lg shadow-teal-700/30 ring-4 ring-teal-50 transition group-hover:scale-105 group-hover:shadow-teal-700/40" aria-hidden="true">
                    {{ $step['num'] }}
                </span>
                <div class="pt-1.5">
                    <h3 class="text-ink font-serif text-xl font-medium">{{ $step['title'] }}</h3>
                    <p class="text-ink-soft mt-2 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>

    <div class="mt-12 text-center">
        <a href="{{ route('booking.index') }}" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
            Prendre rendez-vous
        </a>
    </div>
</x-section>
