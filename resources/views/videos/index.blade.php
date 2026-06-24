@extends('layouts.site')

@php
    $page = (int) request('page', 1);
    $hasCategory = ! empty($activeCategory);
    $hasSearch = ! empty($activeSearch);
    $metaTitle = $hasCategory
        ? 'Vidéos · catégorie '.optional($categories->firstWhere('slug', $activeCategory))->name.' · Vivre Pleinement'
        : 'Toutes les vidéos · Vivre Pleinement';

    if ($page > 1 && ! $hasCategory) {
        $metaTitle = 'Toutes les vidéos (page '.$page.') · Vivre Pleinement';
    }

    $metaDesc = "Toutes les vidéos de Laura Baechlé pour comprendre et apaiser les troubles anxieux : conseils, exercices, témoignages.";

    // Pages indexables : la liste complète et les pages de catégorie (vos
    // landing pages SEO). Les résultats de recherche et les pages 2+ sont en
    // noindex pour éviter le contenu dupliqué et le gaspillage de budget crawl.
    $isIndexable = ! $hasSearch && $page === 1;
@endphp

@section('title', $metaTitle)
@section('description', $metaDesc)
@section('canonical', route('videos.index', $hasCategory ? ['category' => $activeCategory] : []))

@push('head')
    @unless ($isIndexable)
        <meta name="robots" content="noindex, follow">
    @endunless

    @if ($isIndexable && ! $hasCategory)
        @php
            $itemListLd = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Vidéos Vivre Pleinement',
                'itemListElement' => $topVideos->map(fn ($v, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => route('videos.show', $v),
                    'name' => $v->title,
                ])->all(),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($itemListLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
@endpush

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Vidéos'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Vidéos
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Apprendre en vidéo.
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Conseils, exercices et témoignages pour comprendre et apaiser les troubles anxieux.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            @livewire('video-search', ['category' => $activeCategory ?? ''])
        </div>
    </main>

    @include('home.sections.footer')
@endsection
