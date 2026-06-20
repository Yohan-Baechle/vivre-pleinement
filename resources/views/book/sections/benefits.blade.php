@php
    $benefits = [
        "Laisser passer les pensées dérangeantes sans paniquer",
        "Reprendre votre enfant dans les bras sans calculer chaque geste",
        "Sortir, conduire, cuisiner, sans le poids permanent de l'angoisse",
        "Dormir sans ressasser, vous réveiller sans cette boule au ventre",
        "Cesser de chercher sur internet « suis-je un monstre ? »",
        "Retrouver des moments de joie pleins, sans qu'une pensée vienne tout gâcher",
        "Pouvoir en parler enfin, à quelqu'un qui ne vous jugera pas",
        "Vivre vos relations sans la peur de « passer à l'acte »",
    ];
@endphp

<x-section bg="bg-cream-50" eyebrow="Imaginez" title="Et si, dans quelques semaines…">
    <div class="mx-auto max-w-3xl">
        <ul class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
            @foreach ($benefits as $benefit)
                <li class="text-ink flex items-start gap-3 text-base sm:text-lg">
                    <span class="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-teal-700 text-white">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="leading-snug">{{ $benefit }}</span>
                </li>
            @endforeach
        </ul>

        <p class="mt-10 text-center font-serif text-xl text-teal-700 italic sm:text-2xl">
            « Ce n'est pas une promesse magique. C'est un chemin. <br class="hidden sm:block">Et il commence par tourner la première page. »
        </p>
    </div>
</x-section>
