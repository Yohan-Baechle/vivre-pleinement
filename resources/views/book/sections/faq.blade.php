@php
    $faq = [
        [
            'q' => "Ce livre est-il pour moi si je n'ai pas de TOC diagnostiqué ?",
            'a' => "Oui. Le livre s'adresse à toute personne qui souffre de pensées intrusives, qu'il s'agisse d'un TOC diagnostiqué, d'une phobie d'impulsion, de pensées obsessionnelles anxieuses ou simplement d'images mentales dérangeantes que vous n'arrivez pas à chasser. Les méthodes proposées agissent sur le mécanisme commun à tous ces troubles.",
        ],
        [
            'q' => "J'ai déjà essayé les TCC et les antidépresseurs sans succès. Pourquoi ce livre ferait la différence ?",
            'a' => "Parce que l'approche est différente. Là où les TCC s'attaquent souvent frontalement aux pensées, ce livre propose une voie complémentaire : comprendre le terrain, apaiser le système nerveux, transformer le rapport à vos pensées plutôt que les combattre. De nombreuses personnes qui n'avaient pas répondu aux approches classiques y ont trouvé un déclic.",
        ],
        [
            'q' => "C'est un livre numérique ou papier ?",
            'a' => "C'est un PDF de 77 pages, que vous recevez par email immédiatement après votre paiement. Vous pouvez le lire sur ordinateur, smartphone, tablette ou liseuse. Et bien sûr, vous pouvez l'imprimer chez vous si vous préférez le papier &mdash; les 12 fiches pratiques sont d'ailleurs conçues pour ça.",
        ],
        [
            'q' => "Combien de temps faut-il pour voir des résultats ?",
            'a' => "Ça dépend de votre situation. Certaines personnes ressentent un soulagement dès la lecture des premiers chapitres, simplement parce qu'elles comprennent enfin ce qui leur arrive. Pour les changements profonds, comptez plusieurs semaines de pratique régulière des fiches. Ce n'est pas une méthode miracle, c'est un chemin &mdash; mais c'est un chemin qui marche.",
        ],
        [
            'q' => "Le coaching à 70 € est-il vraiment utile en plus du livre ?",
            'a' => "Le livre seul est conçu pour être complet et autonome. Le coaching est pour vous si : vous voulez quelqu'un avec qui en parler sans peur d'être jugé(e), vous avez besoin d'aide pour adapter les fiches à votre situation précise, ou vous voulez accélérer les choses avec un regard extérieur. C'est un complément, pas une obligation.",
        ],
        [
            'q' => "Comment se passe le paiement ? C'est sécurisé ?",
            'a' => "Oui, totalement. Les paiements sont gérés par Stripe et PayPal, les deux plateformes les plus utilisées au monde pour les paiements en ligne. Vous pouvez payer par carte bancaire ou via votre compte PayPal. Sur votre relevé, la mention « Vivre Pleinement » apparaîtra. Aucun renouvellement automatique, aucun abonnement caché.",
        ],
        [
            'q' => "Et si le livre ne me convient pas ?",
            'a' => "Vous avez 30 jours pour le lire, l'essayer, vous faire votre propre idée. Si vous estimez qu'il ne vous a rien apporté, vous m'envoyez un simple email et je vous rembourse intégralement. Sans justification à donner. C'est ma façon de vous dire que je crois en ce livre &mdash; et que le risque, c'est moi qui le prends, pas vous.",
        ],
        [
            'q' => "Vous n'êtes ni médecin ni psychologue. Pourquoi devrais-je vous écouter ?",
            'a' => "Vous avez raison de poser la question. Je ne remplace pas un suivi médical et je le dis clairement dans le livre : si vous êtes en grande souffrance, consultez. Mon expertise vient d'ailleurs : plus de dix ans à vivre ce trouble, à tester des dizaines d'approches, à lire, à me former. C'est l'expertise du vécu &mdash; celle qu'aucun manuel ne contient. À vous de voir si elle peut vous parler.",
        ],
    ];
@endphp

@push('head')
    @php
        $faqLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faq)->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags(html_entity_decode($item['a'], ENT_QUOTES | ENT_HTML5)),
                ],
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

<x-section id="faq" bg="bg-cream-50" eyebrow="Questions fréquentes" title="Tout ce que vous voulez savoir avant d'acheter." lead="Si votre question n'est pas ici, écrivez-moi, je vous réponds personnellement.">
    <div class="mx-auto max-w-3xl space-y-4">
        @foreach ($faq as $item)
            <x-accordion-item :question="$item['q']" :open="$loop->first">
                {!! $item['a'] !!}
            </x-accordion-item>
        @endforeach
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
            <span class="border-b border-teal-700/30">Une autre question ? Écrivez-moi</span>
            <span aria-hidden="true">→</span>
        </a>
    </div>
</x-section>
