@php
    $authorEmail = config('legal.site.contact_email');

    $faq = [
        [
            'q' => "Comment se passe le règlement ? Aurais-je un justificatif d'achat ?",
            'a' => "En cliquant sur le bouton « obtenir le livre + le coaching » ou « obtenir le livre uniquement », vous allez arriver sur une page de paiement sécurisé. Vous devrez remplir les informations demandées pour finaliser la commande. Vous recevrez ensuite un courriel de confirmation de commande directement dans votre boîte mail et bien évidemment, avec le livre numérique à télécharger.",
        ],
        [
            'q' => "Je n'aime pas acheter des produits sur Internet, car je n'ai pas confiance. L'achat est-il vraiment sécurisé ?",
            'a' => "Vous ne risquez absolument rien. Vous pouvez régler soit par PayPal, soit par carte bancaire. Les plateformes de paiement en ligne Stripe et PayPal sont réputées pour pouvoir effectuer des paiements simples et sécurisés.",
        ],
        [
            'q' => "Quelle mention va apparaître sur mon relevé bancaire ?",
            'a' => "Les données remplies lors de la commande restent anonymes pour votre banque. L'achat sur votre compte bancaire portera la mention : vivre pleinement.",
        ],
        [
            'q' => "Si j'opte pour le pack avec coaching, comment cela va-t-il se passer pour prendre rendez-vous ?",
            'a' => "Après avoir validé votre commande et renseigné vos coordonnées, un email vous sera envoyé automatiquement. Cet email contiendra un lien de téléchargement ainsi qu'un code unique. Ce code vous permettra de planifier un rendez-vous de coaching directement sur le site dans la rubrique « Prendre rendez-vous » et la prestation sera affichée à 0 €.",
        ],
        [
            'q' => "Est-ce qu'un accompagnement à distance est aussi efficace qu'un coaching en présentiel ?",
            'a' => "Les coachings à distance se font de plus en plus. Certains coachs arrivent à utiliser l'hypnose à distance, par exemple. Pour quelques cas particuliers, le coaching en présentiel apparaît nécessaire, comme aller se promener avec son coach dans le cadre d'un exercice pour lutter contre la timidité. Autrement, le coaching à distance vaut autant qu'un accompagnement en présentiel. Vous évitez de prendre la voiture, restez chez vous dans votre confort, et surtout : vous pouvez joindre votre coach où que vous vous trouviez dans le monde ! Et certaines personnes, réticentes à aller confier leurs soucis anxieux à un coach, éprouvent moins de difficultés à le faire à distance.",
        ],
        [
            'q' => "Ce n'est qu'un simple fichier PDF et non un livre ?",
            'a' => "Il s'agit bien d'un livre que vous aurez entre vos mains. Celui-ci regroupe de nombreuses solutions inédites pour soulager votre trouble anxieux. C'est juste que cet ouvrage est dématérialisé. Mais cela reste un livre. Vous pouvez d'ailleurs l'imprimer si vous le désirez. Et si vous avez l'habitude de surligner vos livres avec un marqueur pour garder de côté les idées clés, sachez que vous pourrez également le faire sur mon guide dématérialisé.",
        ],
        [
            'q' => "Vous proposez gratuitement une réponse aux questions par mail. Où puis-je vous contacter ?",
            'a' => "Vous pouvez me contacter à l'adresse mail suivante : <a href=\"mailto:{$authorEmail}\" class=\"border-b border-teal-700/30 text-teal-700\">{$authorEmail}</a>",
        ],
        [
            'q' => "Quelle crédibilité avez-vous pour écrire un livre sur les phobies d'impulsion ? Vous n'êtes ni médecin, ni psychologue",
            'a' => "Effectivement. Cependant, j'estime que personne ne peut en comprendre une autre sans avoir expérimenté la même chose. Je connais très bien ce trouble, puisque je l'ai vécu. Et surtout, je m'en suis sortie. Je suis également la première à encourager les personnes venant me consulter à aller voir un psychologue et un médecin s'ils en ressentent le besoin.",
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
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

<x-section id="faq" bg="bg-cream-50" title="Foire aux questions (FAQ)" lead="Vous avez d'autres questions qui n'apparaissent pas ici ? Laissez-moi un message.">
    <div class="mx-auto max-w-3xl space-y-4">
        @foreach ($faq as $item)
            <x-accordion-item :question="$item['q']" :open="$loop->first">
                {!! $item['a'] !!}
            </x-accordion-item>
        @endforeach
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
            <span class="border-b border-teal-700/30">Laissez-moi un message par le biais de mon formulaire de contact</span>
            <span aria-hidden="true">→</span>
        </a>
    </div>
</x-section>
