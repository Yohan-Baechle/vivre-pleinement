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
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @foreach ($testimonials as $t)
            <figure class="ring-ink/5 flex flex-col rounded-3xl bg-white p-7 shadow-xs ring-1">
                <svg class="size-8 text-teal-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M9.5 4.5C6 4.5 3 7.5 3 11v9h6v-9H6c0-2 1.5-4 3.5-4v-2.5zm9 0c-3.5 0-6.5 3-6.5 6.5v9h6v-9h-3c0-2 1.5-4 3.5-4v-2.5z"/>
                </svg>
                <blockquote class="text-ink mt-4 flex-1 font-serif text-lg leading-snug italic">
                    {{ $t['text'] }}
                </blockquote>
                <figcaption class="border-ink/10 mt-6 border-t pt-4">
                    <p class="text-ink text-sm font-medium">{{ $t['author'] }}</p>
                    <p class="text-ink-muted text-xs">{{ $t['context'] }}</p>
                </figcaption>
            </figure>
        @endforeach
    </div>

    <p class="text-ink-muted mt-8 text-center text-xs">
        Témoignages authentiques, publiés avec l'accord des personnes concernées.
    </p>
</x-section>
