@php
    $chapters = [
        ['title' => 'Introduction', 'pages' => 'P. 4 – 5'],
        [
            'title' => 'Chapitre 1 – rappel de ce qu\'est la phobie d\'impulsion',
            'pages' => 'P. 6 – 7',
        ],
        [
            'title' => 'Chapitre 2 – pourquoi souffrez-vous de phobie d\'impulsion ?',
            'pages' => 'P. 8 – 15',
        ],
        [
            'title' => 'Chapitre 3 – les limites des 2 solutions actuelles pour vaincre les phobies d\'impulsion',
            'pages' => 'P. 16 – 21',
        ],
        [
            'title' => 'Chapitre 4 – 12 fiches pratiques à suivre pour guérir de la phobie d\'impulsion',
            'pages' => 'P. 22 – 77',
        ],
    ];
@endphp

<x-section id="sommaire" bg="bg-cream-50" eyebrow="Le sommaire" title="Ce que vous allez trouver dans mon livre" lead="(les bénéfices qu'il va vous offrir)">
    <ol class="mx-auto max-w-3xl space-y-4">
        @foreach ($chapters as $chapter)
            <li class="ring-cream-200 flex items-start justify-between gap-6 rounded-3xl bg-white p-6 ring-1 sm:p-7">
                <h3 class="text-ink font-serif text-lg leading-snug font-medium sm:text-xl">{{ $chapter['title'] }}</h3>
                <p class="mt-1 shrink-0 text-xs font-medium tracking-wider text-teal-700 uppercase">{{ $chapter['pages'] }}</p>
            </li>
        @endforeach
    </ol>

    <p class="text-ink-soft mt-10 text-center text-base sm:text-lg">
        Vous trouverez le détail des 12 fiches pratiques en vous procurant mon livre.
    </p>
</x-section>
