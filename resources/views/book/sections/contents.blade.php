@php
    $chapters = [
        [
            'num' => 'Ch. 1',
            'title' => 'Ce qu\'on vous a jamais expliqué sur les pensées intrusives',
            'pages' => 'p. 6–7',
            'items' => [
                'Phobie d\'impulsion, TOC, pensées intrusives : démêler les mots',
                'Le mécanisme caché : pourquoi votre cerveau s\'y accroche',
            ],
        ],
        [
            'num' => 'Ch. 2',
            'title' => 'Pourquoi vous, pourquoi maintenant',
            'pages' => 'p. 8–15',
            'items' => [
                'Le terrain anxieux : tempérament, vécu, contexte',
                'Le rôle des événements déclencheurs',
                'Pourquoi vous n\'êtes ni faible, ni « à part »',
            ],
        ],
        [
            'num' => 'Ch. 3',
            'title' => 'Pourquoi les solutions classiques échouent souvent',
            'pages' => 'p. 16–21',
            'items' => [
                'Les limites des TCC sur ce trouble spécifique',
                'Les antidépresseurs : ce qu\'on ne vous dit pas',
                'Ce que la médecine n\'aborde pas (et qui fait la différence)',
            ],
        ],
        [
            'num' => 'Ch. 4',
            'title' => '12 fiches pratiques pour vous libérer',
            'pages' => 'p. 22–77',
            'items' => [
                'Fiches 1–3 · Désamorcer les pensées intrusives sur le vif',
                'Fiches 4–6 · Apaiser le corps et le système nerveux',
                'Fiches 7–9 · Sortir des compulsions et de l\'évitement',
                'Fiches 10–12 · Reconstruire confiance et liberté au quotidien',
            ],
        ],
    ];
@endphp

<x-section id="sommaire" bg="bg-white" eyebrow="Le sommaire" title="Ce que vous trouverez dans les 77 pages." lead="Un parcours progressif, du « comprendre » au « faire ».">
    <div class="mx-auto max-w-3xl space-y-4">
        @foreach ($chapters as $chapter)
            <details class="accordion-item group bg-cream-50 ring-cream-200 rounded-3xl p-6 ring-1 transition open:bg-white open:ring-teal-100 sm:p-7">
                <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">{{ $chapter['num'] }} · {{ $chapter['pages'] }}</p>
                        <h3 class="text-ink mt-1 font-serif text-lg leading-snug font-medium sm:text-xl">{{ $chapter['title'] }}</h3>
                    </div>
                    <span class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-teal-700 text-white shadow-sm transition group-open:rotate-45" aria-hidden="true">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </span>
                </summary>
                <div class="accordion-content">
                    <div class="accordion-inner">
                        <ul class="border-ink/10 text-ink-soft mt-5 space-y-2.5 border-t pt-5 text-sm sm:text-base">
                            @foreach ($chapter['items'] as $item)
                                <li class="flex items-start gap-3">
                                    <svg class="mt-1 size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{!! $item !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</x-section>
