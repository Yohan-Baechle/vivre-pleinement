@php
    $testimonials = [
        [
            'text' => 'Quelle chance d\'avoir trouvé quelqu\'un qui comprenne pleinement mon anxiété et mes angoisses, puisqu\'elle a vécu les mêmes. Quelle chance de se dire qu\'on peut en sortir quand on voit le sourire radieux de Laura. Merci de votre bienveillance, de votre écoute et de vos conseils.',
            'author' => 'Angéline',
            'context' => 'Anxiété et angoisses',
        ],
        [
            'text' => 'J\'ai vécu très longtemps dans une souffrance émotionnelle. Dès nos premiers échanges, Laura m\'a écoutée et conseillée. À toutes les personnes qui cherchent l\'apaisement : persévérez. Une écoute bienveillante favorise vraiment la prise de conscience.',
            'author' => 'Jocelyne',
            'context' => 'Souffrance émotionnelle',
        ],
        [
            'text' => 'Notre appel m\'a fait le plus grand bien à un moment où mes émotions étaient en ébullition. C\'est agréable de parler avec une personne qui sait de quoi elle parle. Si vous vous sentez incompris par certains psychologues, n\'hésitez pas à vous tourner vers Laura.',
            'author' => 'Olivia',
            'context' => 'Gestion émotionnelle',
        ],
    ];
@endphp

<x-section
    id="temoignages"
    eyebrow="Témoignages"
    title="Elles en parlent mieux que moi."
    lead="Quelques retours de personnes accompagnées ces dernières années."
    bg="bg-cream-50"
>
    @php
        $offsets = ['lg:mt-0', 'lg:mt-12', 'lg:mt-4'];
    @endphp
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-3">
        @foreach ($testimonials as $i => $t)
            <figure
                data-reveal
                style="--reveal-delay: {{ $i * 140 }}ms"
                class="relative flex flex-col rounded-3xl bg-white/70 p-7 ring-1 ring-ink/5 backdrop-blur-sm {{ $offsets[$i] ?? '' }}"
            >
                <span class="pointer-events-none absolute top-5 right-6 font-serif text-6xl leading-none text-teal-100 select-none" aria-hidden="true">”</span>
                <blockquote class="text-ink relative flex-1 font-serif text-base leading-relaxed italic">
                    {{ $t['text'] }}
                </blockquote>
                <figcaption class="mt-6 flex items-center gap-3 border-t border-ink/8 pt-5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-teal-100 to-teal-50 font-serif text-sm font-medium text-teal-700 ring-1 ring-teal-100" aria-hidden="true">
                        {{ mb_substr($t['author'], 0, 1) }}
                    </span>
                    <span>
                        <span class="text-ink block text-sm font-medium">{{ $t['author'] }}</span>
                        <span class="text-ink-muted block text-xs">{{ $t['context'] }}</span>
                    </span>
                </figcaption>
            </figure>
        @endforeach
    </div>

    <p class="text-ink-muted mt-8 text-center text-xs">
        Témoignages authentiques, publiés avec l'accord des personnes concernées.
    </p>
</x-section>
