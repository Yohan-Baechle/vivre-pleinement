@extends('layouts.site')

@section('title', 'Qui suis-je · Laura Baechlé - Vivre Pleinement')
@section('description', "Laura Baechlé, praticienne ACT. Mon parcours avec les troubles anxieux et comment la thérapie d'acceptation et d'engagement (ACT) m'a permis de vivre pleinement.")
@section('canonical', route('about'))
@section('og_title', 'Qui suis-je · Laura Baechlé')
@section('og_description', "Mon parcours avec les troubles anxieux et la thérapie ACT, que j'exerce aujourd'hui en tant que praticienne.")

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Qui suis-je'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    À propos
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Qui suis-je&nbsp;?
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Je m'appelle Laura et vis en région Lorraine. Voici mon parcours avec les troubles anxieux et comment j'en ai fait une force.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            {{-- Portrait centré en ouverture --}}
            <div class="mx-auto w-full max-w-xs sm:max-w-sm">
                <div class="relative">
                    <div class="rounded-5xl via-cream-100 to-rose-soft/40 absolute inset-0 -z-10 bg-linear-to-br from-teal-100/60 blur-2xl"></div>
                    <div class="bg-cream-100 relative aspect-square overflow-hidden rounded-4xl shadow-2xl ring-8 ring-white">
                        <img
                            src="{{ asset('images/laura-about-800.webp') }}"
                            srcset="{{ asset('images/laura-about-400.webp') }} 400w, {{ asset('images/laura-about-800.webp') }} 800w, {{ asset('images/laura-about-1200.webp') }} 1200w"
                            sizes="(min-width: 640px) 384px, 320px"
                            alt="Laura Baechlé, praticienne ACT spécialisée dans l'accompagnement des troubles anxieux"
                            width="800"
                            height="800"
                            class="size-full object-cover"
                            loading="lazy"
                        >
                    </div>
                </div>
            </div>

            {{-- Récit, centré et calibré pour la lecture --}}
            <article class="mx-auto mt-12 max-w-3xl sm:mt-16">
                    <div class="text-ink-soft space-y-5 text-base leading-relaxed sm:text-lg">
                        <p>
                            D'aussi loin que je me souvienne, j'ai toujours souffert de troubles anxieux. Ayant eu des problèmes de santé étant bébé, mes parents en ont été traumatisés et m'ont ainsi surprotégée à outrance, au point de ne rien pouvoir faire par moi-même. Ce comportement a, pour moi, déclenché mon tempérament anxieux, ayant constamment entendu que j'étais une personne fragile.
                        </p>
                        <p>
                            Durant toute ma primaire, je faisais toujours partie des 1 ou 2 élèves qui n'allaient jamais en classe de neige. J'étais quasiment tout le temps interdite de sortir, trop dangereux… les autres camarades se liaient donc d'amitié sans moi… j'ai dû m'habituer à la solitude, solitude que j'ai réellement fini par apprécier, au point où lorsque je pouvais exceptionnellement sortir, j'en avais bien trop peur. De là s'est enclenché le début de mon agoraphobie.
                        </p>

                        <h2 class="text-ink mt-10 pt-2 font-serif text-2xl font-medium tracking-tight sm:text-3xl">
                            Quand le tempérament devient trouble
                        </h2>
                        <p>
                            À partir du collège, gros changement pour moi et donc, grosse angoisse. À ce moment, mon tempérament anxieux s'est vraiment transformé en trouble anxieux, ce qui m'a fait manquer énormément de cours. J'ai ainsi souffert principalement de&nbsp;:
                        </p>
                        <ul class="space-y-3">
                            <li class="flex gap-3">
                                <span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-teal-500"></span>
                                <span>dépersonnalisation et déréalisation (dus probablement à l'agoraphobie, mais je ne le savais pas à l'époque)&nbsp;;</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-teal-500"></span>
                                <span>émétophobie (peur de vomir)&nbsp;;</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-teal-500"></span>
                                <span>phobie d'impulsion (crainte de faire du mal, de perdre le contrôle d'une situation).</span>
                            </li>
                        </ul>
                        <p>
                            J'ai également subi régulièrement pendant ma scolarité du harcèlement scolaire dû à ma différence, mon physique et mon manque de caractère.
                        </p>
                        <p>
                            Avec toutes ces expériences vécues, je voyais bien que je n'étais pas «&nbsp;comme les autres&nbsp;». Je me trouvais donc anormale.
                        </p>

                        <h2 class="text-ink mt-10 pt-2 font-serif text-2xl font-medium tracking-tight sm:text-3xl">
                            Quand tout a basculé
                        </h2>
                        <p>
                            Alternant entre périodes compliquées et plus calmes, tout a explosé à l'âge de 19 ans, lorsque mon père est décédé. Je pense avoir littéralement décompensé dans ma structure névrotique. Je pleurais souvent, tournais en rond, ne savais pas ce qu'il m'arrivait. Je n'avais plus de passion, plus d'appétit… aurais-je fait une déprime&nbsp;? Cet épisode a duré au moins 6 mois et surtout, a fait apparaître ma peur irrationnelle de devenir folle (psychopatophobie). Cette même période s'est présentée à nouveau en emménageant avec mon compagnon. Tout apparaissait nouveau (nouveau département, nouveau travail). Moi qui me sentais incapable de quitter le «&nbsp;nid&nbsp;» familial, cette transition a vraiment été très difficile à vivre pour moi, bien qu'elle était positive.
                        </p>
                        <p>
                            Par la suite, étant devenue une personne extrêmement contrôlante qui ne supportait pas l'imprévu, j'ai commencé à développer des TOC, notamment superstitieux. Je me disais, par exemple&nbsp;: «&nbsp;si je ne touche pas cet objet, il va arriver malheur à mon conjoint.&nbsp;»
                        </p>

                        <h2 class="text-ink mt-10 pt-2 font-serif text-2xl font-medium tracking-tight sm:text-3xl">
                            Le tournant&nbsp;: la thérapie ACT
                        </h2>
                        <p>
                            J'ai finalement pu réussir à apprivoiser ces troubles anxieux sans qu'ils ne m'handicapent comme auparavant grâce à la thérapie ACT. J'exerce d'ailleurs aujourd'hui en tant que <strong class="text-ink font-medium">praticienne ACT</strong>.
                        </p>
                        <p>
                            L'ACT est une approche thérapeutique dont l'efficacité est <strong class="text-ink font-medium">validée scientifiquement</strong>. Elle fait partie des TCC de 3<sup>e</sup> vague, qui enrichissent les thérapies cognitives et comportementales classiques.
                        </p>

                        <div class="rounded-3xl bg-white p-6 ring-1 ring-ink/5 sm:p-8">
                            <p class="text-ink font-serif text-xl font-medium leading-relaxed sm:text-2xl">
                                J'ai transformé mon expérience avec les troubles anxieux en force afin de vivre ma vie pleinement. Ne vous découragez surtout pas&nbsp;: quel que soit le degré de votre anxiété, il existe une solution à tout&nbsp;!
                            </p>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="mt-12 rounded-3xl bg-teal-700 p-8 text-center shadow-lg shadow-teal-700/20 sm:p-10">
                        <h2 class="font-serif text-2xl font-medium text-white sm:text-3xl">Et si on en parlait&nbsp;?</h2>
                        <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-teal-50 sm:text-base">
                            Quel que soit le degré de votre anxiété, vous n'êtes pas seul·e. Écrivez-moi, je vous réponds personnellement.
                        </p>
                        <a href="{{ route('contact') }}" class="group mt-6 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-teal-700 transition hover:bg-cream-50 sm:text-base">
                            Me contacter
                            <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                        </a>
                    </div>
            </article>
        </div>
    </main>

    @include('home.sections.footer')
@endsection
