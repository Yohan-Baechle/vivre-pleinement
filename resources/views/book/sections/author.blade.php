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
                <span class="size-1.5 rounded-full bg-teal-500"></span>
                L'auteure
            </p>
            <h2 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                Pourquoi me faire confiance ?
            </h2>
            <div class="text-ink-soft mt-6 space-y-4 text-base leading-relaxed">
                <p>
                    Je m'appelle Laura Baechlé. Je ne suis pas médecin, je ne suis pas psychologue. Je suis quelqu'un qui a vécu la phobie d'impulsion et les pensées intrusives de l'intérieur, pendant plus de dix ans, et qui en est sortie.
                </p>
                <p>
                    J'ai testé sur moi-même tout ce qui existe : TCC, médicaments, hypnose, méditation, sophrologie, EMDR, naturopathie, travail somatique, écriture, ennéagramme&hellip; J'ai gardé ce qui marchait vraiment et écarté le reste.
                </p>
                <p>
                    Je suis aussi formée en accompagnement de la pensée positive et de la santé mentale, et j'accompagne aujourd'hui des personnes souffrant d'anxiété généralisée, de phobies et de TOC.
                </p>
                <p class="text-ink">
                    Ce livre, c'est <strong class="font-medium">ce que j'aurais aimé qu'on me donne</strong> quand j'ai commencé à chercher.
                </p>
            </div>

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
