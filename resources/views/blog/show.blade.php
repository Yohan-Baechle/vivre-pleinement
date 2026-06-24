@extends('layouts.site')

@php
    use App\Support\AffiliateLinks;
    use App\Support\Toc;

    $toc = Toc::build($post->content);
    $toc['html'] = AffiliateLinks::enhance($toc['html']);
    $coverMedia = $post->getFirstMedia('featured');
    $cover = $post->featuredImageUrl();
    $category = $post->categories->first();
    $shareUrl = url()->current();
    $shareTitle = $post->title;
@endphp

@section('title', $post->seo_title ?: $post->title.' · Vivre Pleinement')
@section('canonical', route('blog.show', $post->slug))
@section('description', $post->seo_description ?: $post->excerpt)

@push('head')
    @if ($post->seo_robots)
        <meta name="robots" content="{{ $post->seo_robots }}">
    @endif

    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->seo_title ?: $post->title }}">
    <meta property="og:description" content="{{ $post->seo_description ?: $post->excerpt }}">
    <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
    @if ($cover)
        <meta property="og:image" content="{{ $cover }}">
    @endif
    <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() }}">
    @foreach ($post->categories as $c)
        <meta property="article:section" content="{{ $c->name }}">
    @endforeach
    @foreach ($post->tags as $t)
        <meta property="article:tag" content="{{ $t->name }}">
    @endforeach

    @php
        $articleLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->seo_title ?: $post->title,
            'description' => $post->seo_description ?: $post->excerpt,
            'image' => $cover ? [$cover] : [],
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->lastModifiedAt()?->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => 'Laura Baechlé', 'url' => url('/')],
            'publisher' => ['@type' => 'Person', 'name' => 'Laura Baechlé', 'url' => url('/')],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post->slug)],
            'articleSection' => $post->categories->pluck('name')->all(),
            'keywords' => $post->tags->pluck('name')->all(),
            'inLanguage' => 'fr-FR',
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($articleLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main id="main">
    <article class="bg-cream-50">
        <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-10">
                <x-breadcrumb :items="[
                    ['label' => 'Accueil', 'url' => route('home')],
                    ['label' => 'Blog', 'url' => route('blog.index')],
                    $category ? ['label' => $category->name, 'url' => route('blog.category', $category->slug)] : null,
                    ['label' => $post->title],
                ]" />

                <div class="mt-8">
                    @if ($category)
                        <a href="{{ route('blog.category', $category->slug) }}"
                           class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200 transition hover:bg-white">
                            <span class="size-1.5 rounded-full bg-teal-500"></span>
                            {{ $category->name }}
                        </a>
                    @endif

                    <h1 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                        {{ $post->title }}
                    </h1>

                    @if ($post->excerpt)
                        <p class="text-ink-soft mt-5 text-base leading-relaxed sm:text-lg">
                            {{ $post->cleanExcerpt() }}
                        </p>
                    @endif

                    <div class="text-ink-muted mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/laura-portrait-400.webp') }}" alt="" width="32" height="32" class="size-8 rounded-full object-cover ring-2 ring-white" loading="lazy">
                            <span class="text-ink font-medium">Laura Baechlé</span>
                        </div>
                        <span aria-hidden="true">·</span>
                        <time datetime="{{ $post->published_at?->toIso8601String() }}">
                            {{ $post->published_at?->locale('fr')->isoFormat('D MMMM YYYY') }}
                        </time>
                        <span aria-hidden="true">·</span>
                        <span>{{ $post->readingTimeMinutes() }} min de lecture</span>
                    </div>
                </div>
            </div>
        </header>

        @if ($coverMedia)
            <figure class="relative z-10 mx-auto -mt-8 max-w-7xl px-4 sm:-mt-12 sm:px-6 lg:px-10">
                <div class="bg-cream-100 aspect-[16/9] overflow-hidden rounded-4xl shadow-2xl ring-8 ring-white">
                    <x-responsive-image
                        :media="$coverMedia"
                        :alt="$post->title"
                        sizes="(min-width: 1280px) 1216px, 100vw"
                        loading="eager"
                        fetchpriority="high"
                        class="size-full object-cover" />
                </div>
            </figure>
        @endif

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-10">
            @if (count($toc['items']) > 1)
                <details class="ring-ink/5 group mb-10 rounded-3xl bg-white p-5 shadow-xs ring-1 lg:hidden">
                    <summary class="text-ink flex cursor-pointer list-none items-center justify-between font-medium">
                        <span class="text-ink-muted text-xs font-medium tracking-wider uppercase">Sommaire</span>
                        <svg class="text-ink-muted size-5 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>
                    <ol class="mt-4 space-y-2 text-sm">
                        @foreach ($toc['items'] as $item)
                            <li @class(['pl-4' => $item['level'] === 3])>
                                <a href="#{{ $item['id'] }}" class="text-ink-soft transition hover:text-teal-700">
                                    {{ $item['text'] }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif

            <div class="flex flex-col gap-12 lg:flex-row lg:gap-12">
                <div class="prose prose-lg prose-ink max-w-none lg:w-[52rem] lg:shrink-0">
                    {!! $toc['html'] !!}
                </div>

                @if (count($toc['items']) > 1)
                    <aside class="hidden lg:order-last lg:block lg:flex-1">
                        <div class="ring-ink/5 sticky top-28 rounded-3xl bg-white p-5 shadow-xs ring-1">
                            <p class="text-ink-muted text-xs font-medium tracking-wider uppercase">Sommaire</p>
                            <ol class="mt-3 space-y-2 text-sm">
                                @foreach ($toc['items'] as $item)
                                    <li @class(['pl-4' => $item['level'] === 3])>
                                        <a href="#{{ $item['id'] }}" class="text-ink-soft transition hover:text-teal-700">
                                            {{ $item['text'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </aside>
                @endif
            </div>

            <div class="border-ink/10 mt-12 flex flex-wrap items-center justify-between gap-6 border-t pt-8">
                @if ($post->tags->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-ink-muted text-xs font-medium tracking-wider uppercase">Tags&nbsp;:</span>
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('blog.tag', $tag->slug) }}"
                               class="text-ink-soft ring-ink/5 inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium ring-1 transition hover:text-teal-700">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center gap-2" aria-label="Partager cet article">
                    <span class="text-ink-muted text-xs font-medium tracking-wider uppercase">Partager&nbsp;:</span>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareTitle) }}&url={{ urlencode($shareUrl) }}"
                       target="_blank" rel="noopener noreferrer" aria-label="Partager sur Twitter"
                       class="ring-ink/5 text-ink-soft flex size-9 items-center justify-center rounded-full bg-white ring-1 transition hover:bg-teal-700 hover:text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                       target="_blank" rel="noopener noreferrer" aria-label="Partager sur Facebook"
                       class="ring-ink/5 text-ink-soft flex size-9 items-center justify-center rounded-full bg-white ring-1 transition hover:bg-teal-700 hover:text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.51 1.5-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}"
                       target="_blank" rel="noopener noreferrer" aria-label="Partager sur LinkedIn"
                       class="ring-ink/5 text-ink-soft flex size-9 items-center justify-center rounded-full bg-white ring-1 transition hover:bg-teal-700 hover:text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.61 0 4.28 2.38 4.28 5.47v6.27zM5.34 7.43A2.06 2.06 0 1 1 5.33 3.3a2.06 2.06 0 0 1 .01 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>
                    </a>
                    <button type="button" data-copy-url="{{ $shareUrl }}" data-copied="false" aria-label="Copier le lien"
                            class="group ring-ink/5 text-ink-soft relative flex size-9 items-center justify-center rounded-full bg-white ring-1 transition hover:bg-teal-700 hover:text-white data-[copied=true]:bg-teal-700 data-[copied=true]:text-white">
                        {{-- Icône copie (état par défaut) --}}
                        <svg class="size-4 transition group-data-[copied=true]:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                        {{-- Coche (état copié) --}}
                        <svg class="hidden size-4 group-data-[copied=true]:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
                        </svg>
                        {{-- Tooltip --}}
                        <span class="bg-ink pointer-events-none absolute -top-9 left-1/2 -translate-x-1/2 scale-95 rounded-lg px-2.5 py-1 text-xs font-medium whitespace-nowrap text-white opacity-0 transition group-data-[copied=true]:scale-100 group-data-[copied=true]:opacity-100" role="status" aria-live="polite">
                            Copié&nbsp;!
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </article>

    @php
        $commentsOpen = $post->commentsAreOpen();
        $rootCount = $post->comments->count();
        $totalCount = $rootCount + $post->comments->sum(fn ($c) => $c->replies->count());
    @endphp

    @if ($relatedVideo)
        <section class="bg-white py-12 sm:py-16">
            <div class="site-container max-w-3xl">
                <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">À regarder</p>
                <h2 class="text-ink mt-2 font-serif text-2xl font-medium tracking-tight sm:text-3xl">
                    La vidéo sur ce sujet
                </h2>
                <a href="{{ route('videos.show', $relatedVideo->slug) }}" class="mt-6 block">
                    <x-youtube-embed :video="$relatedVideo" class="pointer-events-none" />
                    <p class="text-ink-soft mt-4 inline-flex items-center gap-2 text-sm font-medium transition group-hover:text-teal-700">
                        <span class="border-b border-teal-700/30">Voir la vidéo et son résumé</span> →
                    </p>
                </a>
            </div>
        </section>
    @endif

    @if ($pillar)
        <section class="bg-cream-50 py-12 sm:py-16">
            <div class="site-container">
                <a href="{{ route('blog.show', $pillar->slug) }}" class="group block py-6">
                    <p class="text-ink-muted text-sm">Vous avez aimé cet article&nbsp;?</p>
                    <div class="mt-4 flex items-start gap-5 sm:gap-8">
                        <svg class="mt-1 size-10 shrink-0 text-teal-700/70 transition group-hover:translate-x-1 group-hover:-translate-y-1 group-hover:text-teal-700 sm:size-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M9 7h8v8"/>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-ink font-serif text-3xl leading-tight font-medium tracking-tight transition group-hover:text-teal-800 sm:text-4xl">{{ $pillar->title }}</p>
                            <p class="mt-3 inline-block border-t border-teal-700/30 pt-2 text-xs font-medium tracking-wider text-teal-700 uppercase">Le guide complet du thème →</p>
                        </div>
                    </div>
                </a>
            </div>
        </section>
    @endif

    @if ($similar->isNotEmpty())
        <section class="bg-white py-20 sm:py-24">
            <div class="site-container">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                            <span class="size-1.5 rounded-full bg-teal-500"></span>
                            À lire aussi
                        </p>
                        <h2 class="text-ink mt-4 font-serif text-2xl font-medium tracking-tight sm:text-3xl">
                            Articles similaires
                        </h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="hidden items-center gap-2 text-sm font-medium text-teal-700 hover:text-teal-800 sm:inline-flex">
                        Voir tous les articles
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ($similar as $related)
                        <x-post-card :post="$related" class="relative" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($rootCount > 0 || $commentsOpen)
        <section id="commentaires" class="border-cream-200 bg-cream-50 border-t">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-10">
                <div class="mx-auto max-w-3xl">
                    <header class="flex items-center gap-3">
                        <svg class="size-6 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <h2 class="text-ink font-serif text-2xl font-medium sm:text-3xl">
                            @if ($rootCount > 0)
                                {{ $totalCount }} {{ \Illuminate\Support\Str::plural('commentaire', $totalCount) }}
                            @else
                                Commentaires
                            @endif
                        </h2>
                    </header>

                    @if ($rootCount > 0)
                        <ol class="divide-cream-200 mt-8 divide-y">
                            @foreach ($post->comments as $comment)
                                <li class="py-6 first:pt-0">
                                    <x-comment :comment="$comment" />
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    @if ($commentsOpen)
                        @include('blog.partials.comment-form')
                    @elseif ($rootCount > 0)
                        <p class="text-ink-muted mt-8 text-sm">Les commentaires sont fermés pour cet article.</p>
                    @endif
                </div>
            </div>
        </section>
    @endif
    </main>

    @include('home.sections.footer')
@endsection
