<x-section id="a-propos" bg="bg-white">
    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
        <div class="relative mx-auto w-full max-w-md">
            <div class="rounded-5xl via-cream-100 to-rose-soft/40 absolute inset-0 -z-10 bg-linear-to-br from-teal-100/60 blur-2xl"></div>
            <div class="bg-cream-100 relative aspect-square overflow-hidden rounded-4xl shadow-2xl ring-8 ring-white">
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
                <span class="size-1.5 rounded-full bg-teal-500"></span>
                Qui suis-je
            </p>
            <h2 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                J'ai vécu l'anxiété de l'intérieur.
            </h2>
            <div class="text-ink-soft mt-6 space-y-4 text-base leading-relaxed">
                <p>
                    Pendant des années, j'ai vécu de nombreux troubles anxieux. Je me suis vraiment prise en main le jour où ils sont devenus trop handicapants. Je sais donc précisément ce que vous traversez, parce que j'ai été à votre place.
                </p>
                <p>
                    Le tournant&nbsp;? La découverte de l'<strong class="text-ink font-medium">ACT</strong>, la thérapie d'acceptation et d'engagement. Cette approche a littéralement transformé mon rapport à l'anxiété. C'est pour cela que je suis devenue praticienne ACT&nbsp;: pour transmettre, à mon tour, ce qui m'a aidée.
                </p>
                <p>
                    L'ACT est une approche dont l'efficacité est <strong class="text-ink font-medium">validée scientifiquement</strong>. Elle fait partie des thérapies cognitives et comportementales de 3<sup>e</sup> vague, et vise à réduire l'emprise des mécanismes qui entretiennent la souffrance, en douceur et à votre rythme.
                </p>
            </div>

            <dl class="border-ink/10 mt-8 grid grid-cols-3 gap-4 border-t pt-6">
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Approche</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-teal-700">ACT</dd>
                </div>
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Méthode</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-teal-700">Validée</dd>
                </div>
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Format</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-teal-700">À distance</dd>
                </div>
            </dl>

            <a href="{{ route('contact') }}" class="group mt-8 inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                <span class="border-b border-teal-700/30 transition group-hover:border-teal-700">Me contacter</span>
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</x-section>
