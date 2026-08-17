@php
    $benefits = [
        "Laisser passer facilement vos pensées tordues, car vous savez que tout le monde en a et que cela ne fait pas de vous quelqu'un de bizarre",
        "Profiter pleinement de vos moments de joie sans que des pensées intrusives ne viennent les gâcher",
        "Avoir confiance en vous. Vous savez maintenant que vous êtes une personne normale, comme les autres et que vous ne pourrez jamais passer à l'acte. Vous savez ce que vous valez.",
        "Avoir des relations saines et apaisées avec votre entourage",
        "Aller travailler sans angoisse et avec entrain",
    ];
@endphp

<x-section bg="bg-cream-50" title="Qu'accomplirez-vous en étant guéri ?" lead="Ce guide vous permettra de commencer à changer. Vous allez progressivement désormais :">
    <div class="mx-auto max-w-3xl">
        <ul class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
            @foreach ($benefits as $benefit)
                <li class="text-ink flex items-start gap-3 text-base sm:text-lg">
                    <span class="mt-1 flex size-5 shrink-0 items-center justify-center rounded-full bg-teal-700 text-white">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="leading-snug">{{ $benefit }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</x-section>
