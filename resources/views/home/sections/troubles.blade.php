@php
    $troubles = [
        'trouble anxieux généralisé (TAG)',
        'crises d\'angoisse, anxiété matinale, angoisse nocturne, etc.',
        'phobie spécifique',
        'agoraphobie/phobie sociale',
        'addictions',
        'TOC/phobie d\'impulsion',
        'hypocondrie',
        'manque de confiance et d\'estime de soi',
        'dépersonnalisation / déréalisation',
        'troubles mixtes d\'anxiété (trouble anxio-dépressif)',
        'stress et difficultés liées à la performance au travail',
        'fatigue mentale, burnout',
    ];
@endphp

<x-section
    id="troubles"
    eyebrow="Mon accompagnement"
    title="Sur quels types de troubles anxieux puis-je intervenir ?"
    lead="Je peux vous accompagner dans les troubles et problèmes émotionnels suivants :"
>
    <ul class="mx-auto grid max-w-4xl grid-cols-1 gap-x-8 gap-y-1 sm:grid-cols-2">
        @foreach ($troubles as $trouble)
            <li
                data-reveal
                style="--reveal-delay: {{ $loop->index * 60 }}ms"
                class="group border-ink/8 flex items-center gap-4 border-b py-4 last:border-b-0 sm:[&:nth-last-child(2):nth-child(odd)]:border-b-0"
            >
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-teal-50 text-teal-700 ring-1 ring-teal-100 transition group-hover:bg-teal-100 group-hover:ring-teal-200" aria-hidden="true">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                </span>
                <span class="text-ink text-base leading-snug first-letter:uppercase">{{ $trouble }}</span>
            </li>
        @endforeach
    </ul>

    <div class="mx-auto mt-12 max-w-3xl text-center">
        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
            <a href="{{ route('booking.index') }}" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                Prendre rendez-vous
            </a>
            <a href="{{ route('blog.index') }}" class="text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700 sm:text-base">
                Découvrir mes articles
            </a>
        </div>
    </div>
</x-section>
