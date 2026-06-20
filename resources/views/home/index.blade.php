@extends('layouts.site')

@push('head')
    @php
        $home = url('/');
        $page = url()->current();
        $img  = asset('images/laura-portrait-1200.webp');
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Person',
                    '@id' => $home.'#laura',
                    'name' => 'Laura Baechlé',
                    'jobTitle' => 'Praticienne ACT en accompagnement des troubles anxieux',
                    'url' => $home,
                    'image' => $img,
                    'description' => "Praticienne ACT spécialisée dans l'accompagnement des personnes souffrant de troubles anxieux : anxiété généralisée (TAG), phobies, TOC, burnout.",
                    'knowsAbout' => ['Troubles anxieux', 'Anxiété généralisée', 'TAG', 'Phobies', 'TOC', 'Burnout', 'Thérapie ACT', 'Gestion du stress', 'Bien-être mental'],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $home.'#website',
                    'url' => $home,
                    'name' => 'Vivre Pleinement',
                    'description' => 'Se libérer des troubles anxieux : outils, ressources et accompagnement par Laura Baechlé.',
                    'publisher' => ['@id' => $home.'#laura'],
                    'inLanguage' => 'fr-FR',
                ],
                [
                    '@type' => 'WebPage',
                    '@id' => $page.'#webpage',
                    'url' => $page,
                    'name' => 'Se libérer des troubles anxieux',
                    'isPartOf' => ['@id' => $home.'#website'],
                    'about' => ['@id' => $home.'#laura'],
                    'primaryImageOfPage' => $img,
                    'hasPart' => ['@id' => $home.'#faq'],
                    'inLanguage' => 'fr-FR',
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main id="main">
    <div class="parallax-scene to-cream-50 text-ink relative flex h-svh min-h-160 flex-col overflow-hidden bg-linear-to-b from-teal-200 from-0% via-teal-100 via-30% to-85%">

        <section class="relative isolate flex min-h-0 flex-1">
            {{-- Soleil --}}
            <div class="pointer-events-none absolute inset-0 -z-0 overflow-hidden">
                <div data-parallax="0.02" class="absolute -top-56 -right-40 size-150 rounded-full bg-amber-100/45 blur-3xl will-change-transform"></div>
                <div data-parallax="0.02" class="absolute -top-24 -right-24 size-56 rounded-full bg-linear-to-br from-white via-amber-50 to-amber-100/70 blur-xl will-change-transform sm:size-72"></div>
                <div data-parallax="0.02" class="absolute -top-16 -right-16 size-28 rounded-full bg-linear-to-br from-white to-amber-50 blur-md will-change-transform sm:size-36"></div>
            </div>

            @include('home.partials.birds')

            @include('home.partials.cloud-landscape')

            <div class="relative z-[55] mx-auto flex min-h-0 w-full max-w-3xl flex-1 flex-col items-center justify-center px-4 pt-24 pb-10 text-center sm:px-6 sm:pt-28 lg:pt-32">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200 backdrop-blur-sm">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 4 12l8 10 8-10z"/></svg>
                    Accompagnement anxiété
                </p>

                <h1 class="text-hero text-ink mt-7 font-serif font-medium tracking-tight text-balance sm:mt-8">
                    Retrouvez une vie<br>
                    apaisée, libérée<br>
                    de <span class="font-normal text-teal-700 italic">l'anxiété.</span>
                </h1>

                <p class="text-lead text-ink-soft mx-auto mt-7 max-w-xl text-balance">
                    Anxiété généralisée, phobies, TOC, burnout… Vous n'êtes pas seul·e. Avancez pas à pas, à votre rythme, vers un quotidien plus serein.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                    <a href="#capture" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                        Recevoir la vidéo offerte
                        <span class="transition group-hover:translate-x-0.5">→</span>
                    </a>
                    <a href="#a-propos" class="text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700 sm:text-base">
                        Qui suis-je ?
                    </a>
                </div>
            </div>

            <a href="#troubles" aria-label="Découvrir la suite" class="absolute inset-x-0 bottom-6 z-[90] mx-auto flex w-fit flex-col items-center gap-1 text-teal-700/70 transition hover:text-teal-700">
                <span class="text-[0.7rem] font-medium tracking-wider uppercase">Découvrir</span>
                <svg class="scroll-hint size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
        </section>
    </div>

    @include('home.sections.troubles')
    @include('home.sections.about')
    @include('home.sections.method')
    @include('home.sections.testimonials')
    @include('home.sections.articles')
    @include('home.sections.videos')
    @include('home.sections.faq')

    <div id="capture" class="relative z-10 mx-auto max-w-3xl px-6 pt-12 pb-20 lg:px-10">
        <div class="overflow-hidden rounded-4xl bg-white/80 p-8 shadow-2xl ring-1 ring-white backdrop-blur-md lg:p-10">
            <div class="grid gap-6 lg:grid-cols-5 lg:items-center lg:gap-10">
                <div class="lg:col-span-2">
                    <div class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700">
                        <span class="size-1.5 rounded-full bg-teal-500"></span>
                        Vidéo offerte
                    </div>
                    <h2 class="text-ink mt-3 font-serif text-3xl leading-tight">Les 7 pièges<br>de l'anxiété</h2>
                    <p class="text-ink-soft mt-2 text-sm">Ces 7 pièges m'ont longtemps maintenue dans l'anxiété. Découvrez-les pour ne pas faire les mêmes erreurs et avancer plus vite.</p>
                </div>
                <form action="#" method="POST" class="space-y-3 lg:col-span-3">
                    <input type="text" name="first_name" required placeholder="Prénom" class="bg-cream-100/70 text-ink placeholder:text-ink-muted w-full rounded-2xl border-0 px-5 py-3.5 text-sm focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                    <input type="email" name="email" required placeholder="Votre email" class="bg-cream-100/70 text-ink placeholder:text-ink-muted w-full rounded-2xl border-0 px-5 py-3.5 text-sm focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                    <button type="submit" class="group inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-teal-700 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800">
                        Je reçois ma vidéo
                        <span class="transition group-hover:translate-x-0.5">→</span>
                    </button>
                    <p class="text-ink-muted text-center text-xs">Aucun spam · Désinscription en un clic</p>
                </form>
            </div>
        </div>
    </div>
    </main>

    @include('home.sections.footer')
@endsection
