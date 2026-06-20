@php
    $faq = [
        [
            'q' => 'Comment savoir si je souffre de troubles anxieux ?',
            'a' => "Les troubles anxieux se manifestent par une inquiétude excessive et persistante, des symptômes physiques (palpitations, tensions, fatigue), des évitements qui limitent votre vie quotidienne. Si l'anxiété dure plus de six mois et impacte votre quotidien, il est temps d'en parler. Le rendez-vous découverte gratuit permet justement de faire le point ensemble.",
        ],
        [
            'q' => "Faut-il un diagnostic ou une ordonnance pour commencer ?",
            'a' => "Non, aucun préalable n'est nécessaire. Vous pouvez me contacter directement, sans diagnostic ni ordonnance. Si je perçois qu'un suivi médical serait utile en parallèle, je vous l'indiquerai en toute transparence, mais c'est vous qui décidez de faire le premier pas, quand vous vous sentez prête.",
        ],
        [
            'q' => "En quoi votre accompagnement complète-t-il un suivi médical ?",
            'a' => "La psychiatrie pose un diagnostic médical et peut prescrire des médicaments ; la psychothérapie travaille en profondeur sur les causes. Mon accompagnement s'appuie sur l'ACT (thérapie d'acceptation et d'engagement) pour vous transmettre des outils concrets, applicables au quotidien, afin de réduire l'emprise de l'anxiété. Il ne remplace pas un suivi médical et s'y articule très bien lorsque c'est nécessaire.",
        ],
        [
            'q' => "J'ai déjà suivi une thérapie sans résultat. Pourquoi ce serait différent ?",
            'a' => "Je comprends cette lassitude, et elle est légitime. Beaucoup de personnes que j'accompagne avaient déjà essayé d'autres approches. L'ACT ne cherche pas à supprimer vos pensées ou vos émotions par la volonté, ce qui épuise et finit souvent par échouer, mais à changer votre rapport à elles, pour qu'elles cessent de diriger votre vie. C'est souvent ce déplacement qui fait la différence.",
        ],
        [
            'q' => "Les séances se font-elles en cabinet ou à distance ?",
            'a' => "L'accompagnement se fait à 100% à distance, en visioconférence. Cela permet plus de souplesse dans les horaires, et c'est particulièrement adapté pour les personnes souffrant d'agoraphobie ou de phobie sociale qui peuvent avoir du mal à se déplacer.",
        ],
        [
            'q' => "Comment se déroule une séance ?",
            'a' => "Une séance dure environ une heure, en visio, dans un cadre calme et bienveillant. On part de là où vous en êtes, on explore ensemble ce qui vous pèse, et je vous transmets des outils concrets à expérimenter entre les séances. Rien n'est imposé : vous restez actrice de votre cheminement, à votre rythme.",
        ],
        [
            'q' => "Combien de séances sont nécessaires ?",
            'a' => "Cela dépend de votre situation. En moyenne, un accompagnement complet dure entre 8 et 15 séances, étalées sur 3 à 6 mois. Certaines personnes ressentent des changements significatifs dès les premières séances. On définit ensemble le rythme qui vous convient.",
        ],
        [
            'q' => "Combien coûte un accompagnement ?",
            'a' => "Les tarifs sont précisés lors du rendez-vous découverte gratuit. Je propose plusieurs formules (séance à l'unité, forfait de plusieurs séances) pour s'adapter à votre situation. Les séances ne sont pas remboursées par la Sécurité sociale, mais certaines mutuelles prennent en charge ce type d'accompagnement au titre du bien-être : pensez à vous renseigner auprès de la vôtre.",
        ],
        [
            'q' => "Tout ce que je vous confie reste-t-il confidentiel ?",
            'a' => "Absolument. Tout ce que vous partagez avec moi reste strictement confidentiel et n'est jamais divulgué à qui que ce soit. La confidentialité est le socle de notre travail : c'est ce qui vous permet de parler librement, en toute sécurité.",
        ],
        [
            'q' => "Et si je ne suis pas prête à parler de tout dès la première séance ?",
            'a' => "C'est totalement normal et respecté. On avance à votre rythme, sans pression. Vous ne partagez que ce avec quoi vous êtes à l'aise. La confiance se construit séance après séance.",
        ],
    ];
@endphp

@push('head')
    @php
        $faqLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            '@id' => url('/').'#faq',
            'isPartOf' => ['@id' => url()->current().'#webpage'],
            'mainEntity' => collect($faq)->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

<x-section
    id="faq"
    eyebrow="Questions fréquentes"
    title="Tout ce que vous voulez savoir."
    lead="Les questions qu'on me pose le plus souvent. Si la vôtre n'y est pas, écrivez-moi."
    bg="bg-cream-50"
>
    <div class="mx-auto max-w-3xl space-y-4">
        @foreach ($faq as $item)
            <x-accordion-item :question="$item['q']" :open="$loop->first">
                {{ $item['a'] }}
            </x-accordion-item>
        @endforeach
    </div>
</x-section>
