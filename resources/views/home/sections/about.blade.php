<x-section id="a-propos" bg="bg-white">
    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
        <div class="relative mx-auto w-full max-w-md">
            <div class="via-cream-100 to-rose-soft/40 absolute inset-0 -z-10 rounded-tl-[6rem] rounded-tr-3xl rounded-br-[6rem] rounded-bl-3xl sm:rounded-tl-[9rem] sm:rounded-br-[9rem] bg-linear-to-br from-teal-100/60 blur-2xl"></div>
            <div class="bg-cream-100 relative aspect-square overflow-hidden rounded-tl-[6rem] rounded-tr-3xl rounded-br-[6rem] rounded-bl-3xl sm:rounded-tl-[9rem] sm:rounded-br-[9rem] shadow-2xl ring-8 ring-white">
                <img
                    src="{{ asset('images/laura-about-800.webp') }}"
                    srcset="{{ asset('images/laura-about-400.webp') }} 400w, {{ asset('images/laura-about-800.webp') }} 800w, {{ asset('images/laura-about-1200.webp') }} 1200w"
                    sizes="(min-width: 1024px) 448px, 100vw"
                    alt="Laura Baechlé, praticienne ACT spécialisée dans l'accompagnement des troubles anxieux"
                    width="800"
                    height="800"
                    class="size-full object-cover"
                    loading="lazy"
                >
            </div>
        </div>

        <div>
            <p class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                Qui suis-je
            </p>
            <h2 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                Pourquoi suis-je habilitée à vous accompagner dans la guérison de vos troubles anxieux&nbsp;?
            </h2>
            <div class="text-ink-soft mt-6 space-y-4 text-base leading-relaxed">
                <p>
                    Ayant eu de nombreux troubles anxieux, je me suis réellement prise en main à partir du moment où ils devenaient trop handicapants. Et comment y suis-je parvenue&nbsp;? Grâce à la découverte de l'<strong class="text-ink font-medium">ACT</strong> (thérapie d'acceptation et d'engagement), laquelle a marqué un véritable tournant dans mon parcours.
                </p>
                <p>
                    Aujourd'hui, étant devenue praticienne ACT, c'est avec beaucoup de sens et de conviction que je souhaite transmettre cette approche à mon tour puisque celle-ci a littéralement changé mes troubles anxieux.
                </p>
                <p>
                    L'ACT est une approche thérapeutique dont l'efficacité est <strong class="text-ink font-medium">validée scientifiquement</strong>. Elle fait partie des TCC de 3<sup>e</sup> vague, qui enrichissent les thérapies cognitives et comportementales classiques.
                </p>
                <p>
                    L'objectif&nbsp;? Réduire l'impact des mécanismes et apprentissages qui entretiennent la souffrance psychologique.
                </p>
                <p>
                    En outre, n'ayant que trop connu les troubles anxieux, je ne peux que comprendre ce que vous ressentez, puisque j'ai été à votre place. Comme on dit toujours&nbsp;: il n'y a que quand nous vivons la même situation que l'autre que nous pouvons comprendre ce qu'il ressent.
                </p>
            </div>

            {{-- Trois colonnes dès le mobile laissaient une centaine de pixels
                 par cellule, où « scientifiquement » débordait en serif 24 px.
                 Les colonnes n'arrivent qu'à partir de sm. --}}
            <dl class="border-ink/10 mt-8 grid gap-4 border-t pt-6 sm:grid-cols-3">
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Approche</dt>
                    <dd class="mt-1 font-serif text-xl font-medium text-teal-700 sm:text-2xl">ACT</dd>
                </div>
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Méthode</dt>
                    <dd class="mt-1 font-serif text-xl leading-tight font-medium text-teal-700 sm:text-2xl">Validée<br class="hidden sm:inline">scientifiquement</dd>
                </div>
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Format</dt>
                    <dd class="mt-1 font-serif text-xl font-medium text-teal-700 sm:text-2xl">À distance</dd>
                </div>
            </dl>

            <a href="{{ route('about') }}" class="group mt-8 inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                <span class="border-b border-teal-700/30 transition group-hover:border-teal-700">En savoir plus sur moi&nbsp;?</span>
            </a>
        </div>
    </div>
</x-section>
