@php
    $signs = [
        [
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'title' => 'Des pensées que vous n\'osez dire à personne',
            'desc' => "Faire du mal à votre enfant, à votre conjoint, à vous-même. Des images qui surgissent sans prévenir et vous terrifient, alors que vous n'avez aucune envie de les vivre.",
        ],
        [
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'title' => 'Une angoisse qui occupe toute la place',
            'desc' => "Vous vérifiez, vous évitez, vous ressassez. Vous passez des heures à essayer de « prouver » que vous n'êtes pas « comme ça ». Et plus vous luttez, plus c'est intense.",
        ],
        [
            'icon' => 'M4.318 6.318a4.5 4.5 0 0 0 0 6.364L12 20.364l7.682-7.682a4.5 4.5 0 0 0-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0 0-6.364 0z',
            'title' => 'La peur de devenir un monstre',
            'desc' => "Vous lisez sur internet et vous tombez sur les pires diagnostics. Vous vous demandez si vous êtes dangereux. Personne ne comprend, et vous n'osez plus en parler.",
        ],
        [
            'icon' => 'M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8m0 0V3m0 5h5',
            'title' => 'Le sentiment que ça empire',
            'desc' => "Les TCC n'ont pas marché ou vous ont épuisé. Les antidépresseurs vous engourdissent sans résoudre le fond. Vous voulez une autre approche, mais vous ne savez plus où chercher.",
        ],
    ];
@endphp

<x-section bg="bg-cream-50" eyebrow="Vous vous reconnaissez ?" title="Si une seule de ces phrases vous parle, ce livre est pour vous.">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:gap-6">
        @foreach ($signs as $sign)
            <div class="group ring-ink/5 relative overflow-hidden rounded-3xl bg-white p-6 shadow-xs ring-1 transition hover:shadow-md sm:p-7">
                <div class="flex size-11 items-center justify-center rounded-2xl bg-teal-50 text-teal-700 ring-1 ring-teal-100">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="{{ $sign['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-ink mt-5 font-serif text-xl leading-snug font-medium">{{ $sign['title'] }}</h3>
                <p class="text-ink-soft mt-3 text-sm leading-relaxed sm:text-base">{{ $sign['desc'] }}</p>
            </div>
        @endforeach
    </div>

    <p class="text-ink-soft mt-12 text-center text-base sm:text-lg">
        Vous n'êtes <strong class="text-ink font-medium">ni fou, ni dangereux, ni seul</strong>.<br class="hidden sm:block">
        Ces pensées portent un nom, et elles se soignent.
    </p>
</x-section>
