@php
    $pillars = [
        [
            'num' => '01',
            'title' => 'Comprendre ce qui se passe vraiment',
            'desc' => "Pourquoi votre cerveau s'accroche à ces pensées précisément. Pourquoi plus vous luttez, plus elles reviennent. Comprendre, c'est déjà se libérer à moitié.",
        ],
        [
            'num' => '02',
            'title' => 'Désamorcer les pensées sans les combattre',
            'desc' => "Des techniques simples, à appliquer dès aujourd'hui, pour que les pensées intrusives perdent leur charge. Pas de lutte, pas d'évitement : un nouveau rapport à votre mental.",
        ],
        [
            'num' => '03',
            'title' => 'Apaiser le corps qui retient l\'anxiété',
            'desc' => "Le TOC n'est pas que dans la tête. Apprenez à relâcher les tensions, à calmer le système nerveux et à retrouver un sommeil qui répare.",
        ],
        [
            'num' => '04',
            'title' => 'Reconstruire la confiance jour après jour',
            'desc' => "Pouvoir à nouveau tenir votre enfant, conduire, cuisiner, vivre. Sans calculer chaque geste. Un retour progressif et solide à votre vie d'avant.",
        ],
    ];
@endphp

<x-section bg="bg-cream-50" eyebrow="Le livre" title="Une méthode douce, en quatre temps.">
    <p class="text-ink-soft mx-auto -mt-8 mb-12 max-w-2xl text-center text-base sm:text-lg">
        Le fruit de plus de dix ans de recherches, d'essais et d'erreurs &mdash; condensé dans <strong class="text-ink font-medium">77 pages claires</strong>, avec <strong class="text-ink font-medium">12 fiches pratiques</strong> à appliquer dès la première lecture.
    </p>

    <ol class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
        @foreach ($pillars as $pillar)
            <li class="ring-ink/5 relative rounded-3xl bg-white p-7 shadow-xs ring-1">
                <span class="font-serif text-5xl font-medium text-teal-700/85" aria-hidden="true">{{ $pillar['num'] }}</span>
                <h3 class="text-ink mt-3 font-serif text-xl font-medium">{{ $pillar['title'] }}</h3>
                <p class="text-ink-soft mt-3 text-sm leading-relaxed sm:text-base">{{ $pillar['desc'] }}</p>
            </li>
        @endforeach
    </ol>
</x-section>
