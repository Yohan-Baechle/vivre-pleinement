@php
    $signs = [
        [
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'desc' => "Vous avez des pensées bizarres et avez peur que cela cache une personnalité psychopathique, perverse ou anormale ?",
        ],
        [
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'desc' => "Vous ressentez une angoisse très importante qui vous fait souffrir tous les jours ?",
        ],
        [
            'icon' => 'M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8m0 0V3m0 5h5',
            'desc' => "Vous avez peur que votre état s'aggrave d'année en année ?",
        ],
    ];
@endphp

<x-section bg="bg-cream-50" eyebrow="Avant de lire ce court message" title="Êtes-vous concerné par le TOC de la phobie d'impulsion ?">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 lg:gap-6">
        @foreach ($signs as $sign)
            <div class="group ring-ink/5 relative overflow-hidden rounded-3xl bg-white p-6 shadow-xs ring-1 transition hover:shadow-md sm:p-7">
                <div class="flex size-11 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 ring-1 ring-teal-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="{{ $sign['icon'] }}"/>
                    </svg>
                </div>
                <p class="text-ink mt-5 font-serif text-lg leading-snug">{{ $sign['desc'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="text-ink-soft mx-auto mt-12 max-w-3xl space-y-4 text-base sm:text-lg">
        <p class="text-ink text-center font-medium">Dans ce cas, lisez vite ce qui suit, car&nbsp;:</p>
        <p>
            <span class="text-teal-700" aria-hidden="true">➥</span>
            je suis également passée par là et j'ai réussi à me libérer de cette phobie. Et je vais vous dire comment dans un instant.
        </p>
        <p>
            <span class="text-teal-700" aria-hidden="true">➥</span>
            j'ai ensuite eu envie de rédiger un livre dans lequel je vous dévoile l'ensemble des solutions m'ayant permis d'aller mieux. Mon but est de partager mes expériences avec vous afin que vous puissiez, vous aussi, vous libérer de la phobie d'impulsion.
        </p>
    </div>
</x-section>
