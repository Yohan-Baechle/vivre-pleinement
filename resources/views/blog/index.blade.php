@extends('layouts.site')

@php
    $page = (int) request('page', 1);

    if (! $hasFilters && $page === 1) {
        $metaTitle = 'Blog · Outils et ressources contre l\'anxiété | Vivre Pleinement';
    } elseif ($hasFilters) {
        $metaTitle = 'Blog · résultats - Vivre Pleinement';
    } else {
        $metaTitle = 'Le blog (page '.$page.') · Vivre Pleinement';
    }

    $metaDesc = $hasFilters
        ? "Articles filtrés sur l'anxiété, les phobies, les TOC et le burnout."
        : "Articles, outils et ressources pour comprendre et apaiser les troubles anxieux : anxiété généralisée, phobies, TOC, burnout. Par Laura Baechlé.";

    $ogImage = asset('images/laura-portrait-1200.webp');
@endphp

@section('title', $metaTitle)
@section('description', $metaDesc)
@section('canonical', route('blog.index').($page > 1 ? '?page='.$page : ''))

@section('robots', ($hasFilters || $page > 1) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="Vivre Pleinement - Blog" href="{{ route('blog.rss') }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">

    @if (! $hasFilters && $page === 1)
        @php
            $blogLd = [
                '@context' => 'https://schema.org',
                '@type' => 'Blog',
                '@id' => route('blog.index').'#blog',
                'name' => 'Blog Vivre Pleinement',
                'description' => $metaDesc,
                'url' => route('blog.index'),
                'inLanguage' => 'fr-FR',
            ];

            $itemListLd = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'itemListElement' => $previewPosts
                    ->map(fn ($p, $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'url' => route('blog.show', $p),
                        'name' => $p->title,
                    ])
                    ->all(),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($blogLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
        <script type="application/ld+json">{!! json_encode($itemListLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
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
                ['label' => 'Blog'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    Le blog
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Le blog
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Des articles pour comprendre l'anxiété et avancer à votre rythme.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            @livewire('post-search', [
                'category' => $filters['category'] ?? '',
                'tag' => $filters['tag'] ?? '',
                'sort' => $filters['sort'] ?? 'recent',
            ])
        </div>
    </main>

    {{-- Drawer filtres mobile --}}
    <div id="filters-drawer" data-drawer class="invisible fixed inset-0 z-50 overflow-hidden opacity-0 transition-opacity duration-300 ease-out lg:hidden">
        <button type="button" data-drawer-close class="bg-ink/40 absolute inset-0 backdrop-blur-xs" aria-label="Fermer les filtres"></button>
        <div class="bg-cream-50 absolute inset-y-0 right-0 w-full max-w-sm translate-x-full overflow-y-auto shadow-2xl transition-[translate] duration-300 ease-out motion-reduce:transition-none" data-drawer-panel>
            <div class="border-ink/10 bg-cream-50 sticky top-0 z-10 flex items-center justify-between gap-4 border-b px-6 py-4">
                <p class="text-ink font-serif text-xl font-medium">Filtres</p>
                <button type="button" data-drawer-close class="text-ink-soft ring-ink/5 flex size-9 items-center justify-center rounded-full bg-white ring-1 transition hover:text-teal-700" aria-label="Fermer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-8 p-6">
                @include('blog.partials.sidebar', ['sidebarId' => 'sb-mobile'])
            </div>
        </div>
    </div>

    @include('home.sections.footer')
@endsection
