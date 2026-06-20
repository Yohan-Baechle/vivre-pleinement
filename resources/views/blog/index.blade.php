@extends('layouts.site')

@php
    use App\Support\BlogFilters;

    $chips = BlogFilters::activeChips($filters, $categories, $allTags ?? collect());
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

@push('head')
    @if ($hasFilters || $page > 1)
        <meta name="robots" content="noindex, follow">
    @endif
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
                'itemListElement' => collect($posts->items())
                    ->prepend($featured)
                    ->filter()
                    ->values()
                    ->map(fn ($p, $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'url' => route('blog.show', $p),
                        'name' => $p->title,
                    ])
                    ->all(),
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($blogLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
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
                ['label' => 'Blog'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Le blog
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Comprendre l'anxiété<br>pour mieux la traverser.
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Articles fouillés, outils concrets, témoignages : tout pour apaiser les troubles anxieux, les phobies, les TOC et le burnout.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12">
                <aside class="hidden lg:col-span-3 lg:block">
                    <div class="sticky top-28 space-y-8">
                        @include('blog.partials.sidebar', ['sidebarId' => 'sb-desktop'])
                    </div>
                </aside>

                <div class="lg:col-span-9">
                    @if ($featured)
                        <section aria-label="Article à la une" class="mb-10 lg:mb-12">
                            <div class="flex items-center justify-between gap-4">
                                <p class="inline-flex items-center gap-2 text-xs font-medium tracking-wider text-teal-700 uppercase">
                                    <span class="h-px w-8 bg-teal-700"></span>
                                    À la une
                                </p>
                            </div>
                            <div class="mt-4">
                                <x-post-card :post="$featured" featured class="relative" />
                            </div>
                        </section>
                    @endif

                    <div class="flex items-center justify-between gap-3">
                        <button type="button" data-drawer-open
                                class="text-ink-soft ring-ink/5 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium ring-1 transition hover:text-teal-700 lg:hidden">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 12h18M3 19.5h18"/></svg>
                            Filtres
                            @if (count($chips) > 0)
                                <span class="rounded-full bg-teal-700 px-1.5 text-xs text-white">{{ count($chips) }}</span>
                            @endif
                        </button>

                        <p class="text-ink-soft hidden text-sm lg:block">
                            {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}
                        </p>

                        <form method="GET" action="{{ route('blog.index') }}" class="flex items-center gap-2" data-auto-submit>
                            @foreach (['q', 'category', 'tag'] as $field)
                                @if (! empty($filters[$field]))
                                    <input type="hidden" name="{{ $field }}" value="{{ $filters[$field] }}">
                                @endif
                            @endforeach
                            <label for="sort" class="text-ink-muted text-xs">Trier&nbsp;:</label>
                            <select name="sort" id="sort" class="text-ink ring-ink/10 rounded-xl border-0 bg-white py-1.5 pr-8 pl-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500">
                                <option value="recent" @selected(($filters['sort'] ?? 'recent') === 'recent')>Plus récents</option>
                                <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>Plus anciens</option>
                            </select>
                            <noscript>
                                <button type="submit" class="rounded-xl bg-teal-700 px-3 py-1.5 text-sm font-medium text-white">OK</button>
                            </noscript>
                        </form>
                    </div>

                    <p class="text-ink-muted mt-2 text-sm lg:hidden">
                        {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}
                    </p>

                    @if (count($chips) > 0)
                        <div class="mt-3 flex flex-wrap items-center gap-2 lg:mt-4">
                            <span class="text-ink-muted text-xs font-medium tracking-wider uppercase">Filtres :</span>
                            @foreach ($chips as $chip)
                                <a href="{{ BlogFilters::url('blog.index', $filters, [$chip['key'] => null]) }}"
                                   class="group inline-flex items-center gap-1.5 rounded-full bg-teal-700 px-3 py-1 text-xs font-medium text-white transition hover:bg-teal-800">
                                    <span>{{ $chip['label'] }}</span>
                                    <svg class="size-3 opacity-70 transition group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </a>
                            @endforeach
                            <a href="{{ route('blog.index') }}" class="text-ink-muted text-xs underline-offset-4 transition hover:text-teal-700 hover:underline">
                                Tout effacer
                            </a>
                        </div>
                    @endif

                    @if ($posts->isNotEmpty())
                        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($posts as $post)
                                <x-post-card :post="$post" class="relative" />
                            @endforeach
                        </div>

                        <div class="mt-12">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="border-ink/15 mt-10 rounded-3xl border border-dashed bg-white/60 p-12 text-center">
                            <svg class="text-ink-muted mx-auto size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                            <p class="text-ink mt-4 font-serif text-xl">Aucun article trouvé.</p>
                            <p class="text-ink-soft mt-2 text-sm">Essayez avec d'autres mots-clés ou retirez des filtres.</p>
                            <a href="{{ route('blog.index') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-teal-700 hover:text-teal-800">
                                Réinitialiser les filtres
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    {{-- Drawer filtres mobile --}}
    <div id="filters-drawer" data-drawer class="invisible fixed inset-0 z-50 overflow-hidden opacity-0 transition-opacity duration-300 ease-out lg:hidden">
        <button type="button" data-drawer-close class="bg-ink/40 absolute inset-0 backdrop-blur-xs" aria-label="Fermer les filtres"></button>
        <div class="bg-cream-50 absolute inset-y-0 right-0 w-full max-w-sm translate-x-full overflow-y-auto shadow-2xl transition-[translate] duration-300 ease-out motion-reduce:transition-none" data-drawer-panel>
            <div class="border-ink/10 bg-cream-50 sticky top-0 z-10 flex items-center justify-between gap-4 border-b px-6 py-4">
                <p class="text-ink font-serif text-xl font-medium">Filtres</p>
                <button type="button" data-drawer-close class="text-ink-soft ring-ink/5 flex size-9 items-center justify-center rounded-full bg-white ring-1 transition hover:text-teal-700" aria-label="Fermer">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-8 p-6">
                @include('blog.partials.sidebar', ['sidebarId' => 'sb-mobile'])
            </div>
        </div>
    </div>

    <script>
        (() => {
            const drawer = document.getElementById('filters-drawer');
            if (!drawer) return;
            const panel = drawer.querySelector('[data-drawer-panel]');
            const focusableSel = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
            let lastFocused = null;

            const trapFocus = (e) => {
                if (e.key !== 'Tab') return;
                const focusables = panel.querySelectorAll(focusableSel);
                if (!focusables.length) return;
                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            };

            const open = () => {
                lastFocused = document.activeElement;
                drawer.classList.remove('invisible');
                document.body.classList.add('overflow-hidden');
                drawer.setAttribute('aria-modal', 'true');
                drawer.setAttribute('role', 'dialog');
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    drawer.classList.remove('opacity-0');
                    panel.classList.remove('translate-x-full');
                    panel.querySelector(focusableSel)?.focus({ preventScroll: true });
                }));
                document.addEventListener('keydown', trapFocus);
            };
            const close = () => {
                drawer.classList.add('opacity-0');
                panel.classList.add('translate-x-full');
                document.body.classList.remove('overflow-hidden');
                document.removeEventListener('keydown', trapFocus);
                setTimeout(() => drawer.classList.add('invisible'), 300);
                lastFocused?.focus();
            };
            document.querySelectorAll('[data-drawer-open]').forEach(el => el.addEventListener('click', open));
            document.querySelectorAll('[data-drawer-close]').forEach(el => el.addEventListener('click', close));
            document.addEventListener('keydown', e => e.key === 'Escape' && !drawer.classList.contains('invisible') && close());

            document.querySelectorAll('[data-auto-submit] select').forEach(select => {
                select.addEventListener('change', () => select.form.submit());
            });
        })();
    </script>

    @include('home.sections.footer')
@endsection
