@props([
    'video' => null,
    'youtubeId' => null,
    'title' => 'Vidéo YouTube',
    'thumbnail' => null,
    'priority' => false,
])

@php
    $ytId = $video?->youtube_id ?? $youtubeId;
    $ytTitle = $video?->title ?? $title;
    $ytThumbnail = $video?->thumbnail() ?? $thumbnail ?? "https://i.ytimg.com/vi/{$ytId}/maxresdefault.jpg";
    $ytDuration = $video?->durationFormatted();
@endphp

@if ($ytId)
    <figure {{ $attributes->class(['youtube-facade group relative block aspect-video w-full overflow-hidden rounded-3xl bg-ink shadow-lg']) }}
            data-youtube-id="{{ $ytId }}"
            data-youtube-title="{{ $ytTitle }}">

        {{-- Thumbnail : léger zoom permanent pour absorber les éventuelles
             bandes noires (letterbox) intégrées par YouTube sur certaines miniatures. --}}
        <img src="{{ $ytThumbnail }}"
             alt="Miniature de la vidéo : {{ $ytTitle }}"
             loading="{{ $priority ? 'eager' : 'lazy' }}"
             @if ($priority) fetchpriority="high" @endif
             width="1280" height="720"
             class="absolute inset-0 size-full scale-[1.04] object-cover transition duration-500 group-hover:scale-[1.08]">

        {{-- Overlay --}}
        <div class="from-ink/60 via-ink/20 group-hover:from-ink/70 absolute inset-0 bg-linear-to-t to-transparent transition"></div>

        {{-- Bouton Play --}}
        <button type="button"
                class="absolute inset-0 flex w-full cursor-pointer items-center justify-center text-white"
                aria-label="Lire la vidéo : {{ $ytTitle }}">
            <span class="flex size-16 items-center justify-center rounded-full bg-teal-700 shadow-2xl ring-4 shadow-teal-900/50 ring-white/20 transition group-hover:scale-110 group-hover:bg-teal-800 sm:size-20">
                <svg class="ml-1 size-7 sm:size-8" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </span>
        </button>

        {{-- Titre + durée --}}
        <figcaption class="pointer-events-none absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-4 sm:p-5">
            <p class="font-serif text-base leading-tight font-medium text-white sm:text-lg">{{ $ytTitle }}</p>
            @if ($ytDuration)
                <span class="shrink-0 rounded-full bg-black/50 px-2 py-1 font-mono text-xs text-white backdrop-blur-xs">{{ $ytDuration }}</span>
            @endif
        </figcaption>
    </figure>
@endif
