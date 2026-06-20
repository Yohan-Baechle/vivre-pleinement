@extends('layouts.site')

@php
    use Illuminate\Support\Str;

    $category = $video->categories->first();
    $metaDescription = $video->metaDescription(160) ?? 'Vidéo de Laura Baechlé sur '.$video->title.'.';
    $ogDescription = $video->metaDescription(200) ?? $metaDescription;
    $schemaDescription = $video->summary
        ?: $video->seo_description
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
    <meta name="twitter:card" content="player">

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
            'publisher' => [
                '@type' => 'Person',
                'name' => 'Laura Baechlé',
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
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-10">
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

        <div class="mx-auto -mt-2 max-w-5xl px-4 sm:px-6 lg:px-10">
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
            <section class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="chapters-heading">
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
            <section class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="takeaways-heading">
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
            <section class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-10" aria-labelledby="transcript-heading">
                <details class="group ring-ink/5 overflow-hidden rounded-2xl bg-white ring-1 [&_summary::-webkit-details-marker]:hidden">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <h2 id="transcript-heading" class="text-ink font-serif text-2xl font-medium">
                            Transcription
                        </h2>
                        <svg class="text-ink-muted size-5 shrink-0 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>
                    <div class="prose prose-ink border-ink/10 max-w-none border-t px-5 py-5 sm:px-6">
                        {!! $video->transcript !!}
                    </div>
                </details>
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
