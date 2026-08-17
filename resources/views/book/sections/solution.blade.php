@php
    $reasons = [
        [
            'num' => '01',
            'desc' => "Votre tempérament anxieux vient très certainement de vos parents.",
        ],
        [
            'num' => '02',
            'desc' => "Vous avez vécu plus d'expériences négatives que d'autres (harcèlement scolaire, décès précoces, etc.).",
        ],
        [
            'num' => '03',
            'desc' => "La société a un problème avec la sensibilité. Il faut à tout prix paraître fort, confiant, sans faille.",
        ],
    ];
@endphp

<x-section bg="bg-cream-50" eyebrow="Écoutez" title="Vous n'êtes pas faible.">
    <p class="text-ink-soft mx-auto -mt-8 mb-12 max-w-2xl text-center text-base sm:text-lg">
        Ce qui vous arrive n'est pas de votre faute. C'est juste que&nbsp;:
    </p>

    <ol class="grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-8">
        @foreach ($reasons as $reason)
            <li class="ring-ink/5 relative rounded-3xl bg-white p-7 shadow-xs ring-1">
                <span class="font-serif text-5xl font-medium text-teal-700/85" aria-hidden="true">{{ $reason['num'] }}</span>
                <p class="text-ink-soft mt-3 text-sm leading-relaxed sm:text-base">{{ $reason['desc'] }}</p>
            </li>
        @endforeach
    </ol>

    <div class="text-ink-soft mx-auto mt-12 max-w-3xl space-y-4 text-base sm:text-lg">
        <p class="text-ink font-medium">Mais aussi que&nbsp;:</p>
        <p>
            Vous n'avez pas eu les bonnes informations pour aller mieux, car vous ne connaissiez pas mon livre.
        </p>
        <p class="text-ink font-medium">Ainsi, en vous procurant mon guide, vous allez recevoir&nbsp;:</p>
        <p>
            <span class="text-teal-700" aria-hidden="true">➥</span>
            Des conseils pour vous libérer de votre phobie d'impulsion. Les solutions que je propose sont naturelles et certaines, inédites.
        </p>
        <p>
            <span class="text-teal-700" aria-hidden="true">➥</span>
            Un suivi par mail compris dans l'achat de mon livre si vous avez la moindre question à me poser.
        </p>
        <p>
            Vous saurez aussi comment, en quelques jours, vous allez déjà aller mieux. Puis, progressivement, vous libérer de votre phobie d'impulsion.
        </p>
        <p>
            Vous allez mettre la main sur un guide qui vous permettra, au fur et à mesure, de vivre sans vous poser autant de questions, et sans gâcher vos moments de joie.
        </p>
    </div>

    <div class="mx-auto mt-16 max-w-3xl">
        <div class="ring-ink/5 rounded-4xl bg-white p-8 shadow-xs ring-1 sm:p-10">
            <h3 class="text-ink font-serif text-2xl leading-snug font-medium sm:text-3xl">
                Mettez toutes les chances de votre côté, allez plus loin que mon livre&nbsp;!
            </h3>
            <div class="text-ink-soft mt-5 space-y-4 text-base leading-relaxed sm:text-lg">
                <p>
                    Mon livre, à lui seul, va vous donner de nombreuses solutions pour aller mieux. Il sera toujours là pour vous accompagner.
                </p>
                <p>
                    Le problème, c'est que même si vous suivez ce guide, vous risquez de vivre des moments de doutes et de rechutes. Vous pourriez alors vous démotiver et abandonner l'idée d'aller mieux.
                </p>
                <p>
                    C'est pourquoi je vous invite à vous procurer le pack coaching + livre. En effet, l'information seule ne suffit pas&nbsp;: il vous faut un encadrement pour vous investir.
                </p>
                <p>
                    En étant atteint du TOC de la phobie d'impulsion, vous savez ce que cela fait de souffrir depuis longtemps. Donc, autant mettre toutes les chances de votre côté pour aller mieux. En optant pour le pack coaching + livre, vous mettrez ainsi toutes les chances de votre côté.
                </p>
            </div>
        </div>
    </div>
</x-section>
