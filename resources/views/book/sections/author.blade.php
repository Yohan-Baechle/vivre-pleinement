<x-section bg="bg-white">
    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-5 lg:gap-16">
        <div class="lg:col-span-2">
            <div class="relative mx-auto w-full max-w-sm">
                <div class="via-cream-100 to-rose-soft/40 absolute inset-0 -z-10 rounded-full bg-linear-to-br from-teal-100/60 blur-2xl"></div>
                <div class="bg-cream-100 relative aspect-square overflow-hidden rounded-full shadow-2xl ring-8 ring-white">
                    <img
                        src="{{ asset('images/laura-livre-800.webp') }}"
                        srcset="{{ asset('images/laura-livre-400.webp') }} 400w, {{ asset('images/laura-livre-800.webp') }} 800w, {{ asset('images/laura-livre-1200.webp') }} 1200w"
                        sizes="(min-width: 1024px) 384px, 100vw"
                        alt="Laura Baechlé, auteure du livre"
                        width="800"
                        height="800"
                        class="size-full object-cover"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <p class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                L'auteure
            </p>
            <h2 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                Laura Baechlé
            </h2>
            <p class="text-ink-muted mt-3 text-sm sm:text-base">
                Auteure du site Vivre-Pleinement.fr et du livre <em>Soigner le TOC de la phobie d'impulsion à l'aide de traitements naturels</em>
            </p>
            <div class="text-ink-soft mt-6 space-y-4 text-base leading-relaxed">
                <p>
                    Dans mon enfance, on m'a toujours dit que j'étais fragile. Et à force de répéter quelque chose à un enfant, il finit par le croire. Je l'ai donc cru et j'ai développé de multiples troubles anxieux. C'est lorsque ceux-ci sont devenus bien trop handicapants que je me suis sérieusement prise en main. J'ai été voir de nombreux psychothérapeutes, dévoré énormément de livres de développement personnel, pratiqué des médecines alternatives&hellip; je me suis introspectée et j'ai effectué un immense travail sur moi-même.
                </p>
                <p>
                    Depuis, j'ai obtenu un certificat en pensée positive et en santé mentale.
                </p>
                <p class="text-ink">Nous avons donc beaucoup de choses en commun.</p>
                <p>
                    Sauf que j'ai mis plus de 10 ans à récolter de nombreuses informations pour guérir. En arrivant assez tôt, ce livre aurait pu me soigner bien plus vite. Voici pourquoi j'ai condensé, dans un guide, toutes les solutions m'ayant permis d'aller mieux. Avec lui, vous n'allez pas prendre 10 ans comme moi pour vous libérer de votre TOC. Vous allez gagner énormément de temps.
                </p>
            </div>

            <ul class="border-ink/10 text-ink-soft mt-8 space-y-4 border-t pt-6 text-sm leading-relaxed sm:text-base">
                <li>
                    <span class="text-teal-700" aria-hidden="true">➥</span>
                    Ce livre est numérique. Il se trouve en format PDF. Vous pourrez ainsi le lire sur votre ordinateur, votre téléphone portable, ou votre tablette. Vous pouvez aussi l'imprimer.
                </li>
                <li>
                    <span class="text-teal-700" aria-hidden="true">➥</span>
                    C'est un livre très agréable à lire. Il comporte 77 pages. J'ai souhaité que chaque ligne de mon ouvrage puisse vous aider. J'ai donc évité le remplissage inutile. De cette façon, vous trouverez rapidement des solutions pour aller mieux.
                </li>
                <li>
                    <span class="text-teal-700" aria-hidden="true">➥</span>
                    Dès que vous vous serez procuré mon guide, vous pourrez immédiatement le télécharger dans votre boîte mail.
                </li>
            </ul>

            <dl class="border-ink/10 mt-8 grid grid-cols-3 gap-4 border-t pt-6">
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Vécu</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-teal-700">10+ ans</dd>
                </div>
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Pages</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-teal-700">77</dd>
                </div>
                <div>
                    <dt class="text-ink-muted text-xs font-medium tracking-wider uppercase">Fiches</dt>
                    <dd class="mt-1 font-serif text-2xl font-medium text-teal-700">12</dd>
                </div>
            </dl>
        </div>
    </div>
</x-section>
