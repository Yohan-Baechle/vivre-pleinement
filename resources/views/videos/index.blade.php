@extends('layouts.site')

@php
    $page = (int) request('page', 1);
    $hasFilters = ! empty($activeCategory);
    $metaTitle = $hasFilters
        ? 'Vidéos · catégorie '.optional($categories->firstWhere('slug', $activeCategory))->name.' · Vivre Pleinement'
        : 'Toutes les vidéos · Vivre Pleinement';

    if ($page > 1 && ! $hasFilters) {
        $metaTitle = 'Toutes les vidéos (page '.$page.') · Vivre Pleinement';
    }

    $metaDesc = "Toutes les vidéos de Laura Baechlé pour comprendre et apaiser les troubles anxieux : conseils, exercices, témoignages.";
@endphp

@section('title', $metaTitle)
@section('description', $metaDesc)
@section('canonical', route('videos.index').($page > 1 ? '?page='.$page : ''))

@push('head')
    @if ($hasFilters || $page > 1)
        <meta name="robots" content="noindex, follow">
    @endif

    @if (! $hasFilters && $page === 1)
        @php
            $itemListLd = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Vidéos Vivre Pleinement',
                'itemListElement' => collect($videos->items())->map(fn ($v, $i) => [
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
                    Conseils, exercices et témoignages pour comprendre et apaiser l'anxiété, les phobies, les TOC et le burnout.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            @if ($categories->isNotEmpty())
                <nav class="mb-10 flex flex-wrap items-center gap-2" aria-label="Filtres par catégorie">
                    <a href="{{ route('videos.index') }}"
                       @class([
                           'inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition',
                           'bg-teal-700 text-white shadow shadow-teal-700/20' => ! $activeCategory,
                           'bg-white text-ink-soft ring-1 ring-ink/5 hover:text-teal-700' => $activeCategory,
                       ])>
                        Toutes ({{ $videos->total() }})
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('videos.index', ['category' => $category->slug]) }}"
                           @class([
                               'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
                               'bg-teal-700 text-white shadow shadow-teal-700/20' => $activeCategory === $category->slug,
                               'bg-white text-ink-soft ring-1 ring-ink/5 hover:text-teal-700' => $activeCategory !== $category->slug,
                           ])>
                            {{ $category->name }}
                            <span @class([
                                'rounded-full px-1.5 text-xs',
                                'bg-white/20' => $activeCategory === $category->slug,
                                'bg-cream-200 text-ink-muted' => $activeCategory !== $category->slug,
                            ])>{{ $category->videos_count }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            @if ($videos->isNotEmpty())
                <h2 class="sr-only">Liste des vidéos</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($videos as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $videos->links() }}
                </div>
            @else
                <div class="border-ink/15 rounded-3xl border border-dashed bg-white/60 p-12 text-center">
                    <svg class="text-ink-muted mx-auto size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m10 9 5 3-5 3z"/>
                    </svg>
                    <p class="text-ink mt-4 font-serif text-xl">Aucune vidéo pour l'instant.</p>
                    <p class="text-ink-soft mt-2 text-sm">Les vidéos arriveront bientôt - revenez nous voir !</p>
                </div>
            @endif
        </div>
    </main>

    @include('home.sections.footer')
@endsection
