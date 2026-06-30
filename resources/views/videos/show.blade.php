@extends('layouts.site')

@php
    use Illuminate\Support\Str;

    $category = $video->categories->first();
    $metaDescription = $video->metaDescription(160) ?? 'Vidéo de Laura Baechlé sur '.$video->title.'.';
    $ogDescription = $video->metaDescription(200) ?? $metaDescription;
    $schemaDescription = $video->summary
        ?: $video->seo_description
        ?: ($video->intro ? strip_tags($video->intro) : null)
        ?: $video->description;
    $chapters = $video->chaptersForSchema();
@endphp

@section('title', $video->title.' · Vidéo Vivre Pleinement')
@section('canonical', route('videos.show', $video->slug))
@section('description', $metaDescription)
@section('og_type', 'video.other')
@section('og_title', $video->title)
@section('og_description', $ogDescription)
@section('og_image', $video->thumbnail())

@push('head')
    @php
        $videoLd = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $video->title,
            'description' => $schemaDescription ? Str::limit(strip_tags($schemaDescription), 5000) : $video->title,
            'thumbnailUrl' => $video->thumbnail(),
            'uploadDate' => $video->published_at?->toIso8601String(),
            'embedUrl' => $video->embedUrl(),
            'contentUrl' => $video->youtubeUrl(),
            'duration' => $video->duration_seconds ? 'PT'.$video->duration_seconds.'S' : null,
            'interactionStatistic' => $video->view_count ? [
                '@type' => 'InteractionCounter',
                'interactionType' => ['@type' => 'WatchAction'],
                'userInteractionCount' => $video->view_count,
            ] : null,
            'author' => [
                '@type' => 'Person',
                'name' => 'Laura Baechlé',
                'url' => url('/'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Vivre Pleinement',
                'url' => url('/'),
            ],
            'inLanguage' => 'fr-FR',
        ];

        if ($video->transcript) {
            $videoLd['transcript'] = strip_tags($video->transcript);
        }

        if (! empty($chapters)) {
            $videoLd['hasPart'] = array_map(fn ($c) => array_filter([
                '@type' => 'Clip',
                'name' => $c['name'],
                'startOffset' => $c['startOffset'],
                'endOffset' => $c['endOffset'],
                'url' => $c['url'],
            ], fn ($v) => $v !== null), $chapters);
        }

        $videoLd = array_filter($videoLd, fn ($v) => $v !== null && $v !== '');
    @endphp
    <script type="application/ld+json">{!! json_encode($videoLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main id="main">

    <article class="bg-cream-50">
        <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-8 sm:pt-36">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-10">
                <x-breadcrumb :items="[
                    ['label' => 'Accueil', 'url' => route('home')],
                    ['label' => 'Vidéos', 'url' => route('videos.index')],
                    ['label' => $video->title],
                ]" />

                <div class="mt-8 max-w-3xl">
                    @if ($category)
                        <a href="{{ route('videos.index', ['category' => $category->slug]) }}"
                           class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200 transition hover:bg-white">
                            <span class="size-1.5 rounded-full bg-teal-500"></span>
                            {{ $category->name }}
                        </a>
                    @endif

                    <h1 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                        {{ $video->title }}
                    </h1>

                    <div class="text-ink-muted mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                        <time datetime="{{ $video->published_at?->toIso8601String() }}">
                            {{ $video->published_at?->locale('fr')->isoFormat('D MMMM YYYY') }}
                        </time>
                        @if ($duration = $video->durationFormatted())
                            <span aria-hidden="true">·</span>
                            <span>{{ $duration }}</span>
                        @endif
                        @if ($video->view_count)
                            <span aria-hidden="true">·</span>
                            <span>{{ number_format($video->view_count, 0, ',', ' ') }} vues</span>
                        @endif
                    </div>

                    @if ($video->summary)
                        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                            {{ $video->summary }}
                        </p>
                    @endif
                </div>
            </div>
        </header>

        @if ($video->intro)
            <div class="mx-auto max-w-5xl px-4 pt-8 sm:px-6 lg:px-10">
                <div class="prose prose-ink max-w-none text-lg leading-relaxed">
                    {!! $video->intro !!}
                </div>
            </div>
        @endif

        <div class="mx-auto mt-8 max-w-5xl px-4 sm:px-6 lg:px-10">
            <x-youtube-embed :video="$video" priority />

            <div class="mt-4 flex justify-end">
                <a href="{{ $video->youtubeUrl() }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.12C19.55 3.5 12 3.5 12 3.5s-7.55 0-9.4.58A3 3 0 0 0 .5 6.2C0 8.05 0 12 0 12s0 3.95.5 5.8a3 3 0 0 0 2.1 2.12c1.85.58 9.4.58 9.4.58s7.55 0 9.4-.58a3 3 0 0 0 2.1-2.12C24 15.95 24 12 24 12s0-3.95-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z"/></svg>
                    Voir sur YouTube
                </a>
            </div>
        </div>

        @if (! empty($chapters))
            <section class="mx-auto max-w-5xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="chapters-heading">
                <h2 id="chapters-heading" class="text-ink font-serif text-2xl font-medium">
                    Chapitres
                </h2>
                <ol class="divide-ink/10 ring-ink/5 mt-4 divide-y rounded-2xl bg-white ring-1">
                    @foreach ($chapters as $chapter)
                        <li>
                            <a href="{{ $chapter['url'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="flex items-center gap-4 px-5 py-3 text-sm transition hover:bg-teal-50">
                                <span class="font-mono text-xs font-medium text-teal-700 tabular-nums">
                                    {{ $video->durationFormatted() && $chapter['startOffset'] >= 3600
                                        ? sprintf('%d:%02d:%02d', intdiv($chapter['startOffset'], 3600), intdiv($chapter['startOffset'] % 3600, 60), $chapter['startOffset'] % 60)
                                        : sprintf('%d:%02d', intdiv($chapter['startOffset'], 60), $chapter['startOffset'] % 60) }}
                                </span>
                                <span class="text-ink">{{ $chapter['name'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if (! empty($video->key_takeaways))
            <section class="mx-auto max-w-5xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="takeaways-heading">
                <h2 id="takeaways-heading" class="text-ink font-serif text-2xl font-medium">
                    À retenir
                </h2>
                <ul class="mt-6 space-y-4">
                    @foreach ($video->key_takeaways as $takeaway)
                        @if (! empty($takeaway['title']))
                            <li class="ring-ink/5 flex gap-4 rounded-2xl bg-white p-5 ring-1">
                                <span class="mt-1 flex size-6 flex-none items-center justify-center rounded-full bg-teal-100 text-xs font-semibold text-teal-700">
                                    {{ $loop->iteration }}
                                </span>
                                <div>
                                    <h3 class="text-ink font-medium">{{ $takeaway['title'] }}</h3>
                                    @if (! empty($takeaway['content']))
                                        <p class="text-ink-soft mt-1 text-sm leading-relaxed">{{ $takeaway['content'] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($video->transcript)
            <section class="mx-auto max-w-5xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="transcript-heading">
                <h2 id="transcript-heading" class="text-ink font-serif text-2xl font-medium">
                    Transcription
                </h2>

                {{-- Aperçu repliable sans JS : la transcription complète reste
                     toujours dans le DOM (indexée par Google) ; la case à cocher
                     ne fait que révéler la partie masquée pour le visiteur. --}}
                <div class="ring-ink/5 relative mt-4 overflow-hidden rounded-2xl bg-white ring-1">
                    <input type="checkbox" id="transcript-toggle" class="peer sr-only">

                    <div class="prose prose-ink max-h-[22rem] max-w-none overflow-hidden p-5 transition-[max-height] duration-500 peer-checked:max-h-none sm:px-6">
                        {!! $video->transcript !!}
                    </div>

                    {{-- Dégradé de fondu sur l'aperçu, masqué une fois déplié.
                         Frère direct de la case à cocher pour que peer-checked
                         s'applique. --}}
                    <div class="from-white pointer-events-none absolute inset-x-0 bottom-14 h-24 bg-gradient-to-t to-transparent peer-checked:hidden"></div>

                    <div class="border-ink/10 border-t p-4 text-center peer-checked:hidden">
                        <label for="transcript-toggle"
                               class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                            Lire la transcription complète
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>
                        </label>
                    </div>
                </div>
            </section>
        @endif

        @if ($relatedPost)
            <section class="mx-auto max-w-5xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="related-post-heading">
                <h2 id="related-post-heading" class="text-ink font-serif text-2xl font-medium">
                    À lire aussi
                </h2>
                <a href="{{ route('blog.show', $relatedPost->slug) }}"
                   class="group ring-ink/5 mt-4 flex items-center gap-5 rounded-2xl bg-white p-5 ring-1 transition hover:ring-teal-200">
                    @if ($cover = $relatedPost->featuredImageUrl('thumb'))
                        <img src="{{ $cover }}" alt="" loading="lazy" width="96" height="96"
                             class="size-20 flex-none rounded-xl object-cover sm:size-24">
                    @endif
                    <div class="min-w-0">
                        <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">Article</p>
                        <p class="text-ink mt-1 font-serif text-lg leading-snug font-medium transition group-hover:text-teal-700">
                            {{ $relatedPost->title }}
                        </p>
                        <p class="text-ink-soft mt-2 inline-flex items-center gap-1.5 text-sm">
                            <span class="border-b border-teal-700/30">Lire l'article complet</span> →
                        </p>
                    </div>
                </a>
            </section>
        @endif

        <div class="pb-12 sm:pb-16"></div>
    </article>

    @if ($related->isNotEmpty())
        <section class="bg-white py-20 sm:py-24">
            <div class="site-container">
                <h2 class="text-ink font-serif text-2xl font-medium tracking-tight sm:text-3xl">
                    Vidéos similaires
                </h2>
                <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $rel)
                        <x-video-card :video="$rel" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    </main>

    @include('home.sections.footer')
@endsection
