@props([
    'video',
])

@php
    $url = route('videos.show', $video);
    $category = $video->categories->first();
@endphp

<article {{ $attributes->class([
    'group relative flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md hover:shadow-teal-700/5 hover:ring-teal-200/60',
]) }}>
    <a href="{{ $url }}" class="bg-ink relative block aspect-video overflow-hidden" tabindex="-1" aria-hidden="true">
        <img src="{{ $video->thumbnail() }}"
             alt=""
             loading="lazy"
             width="1280" height="720"
             class="absolute inset-0 size-full scale-[1.04] object-cover transition duration-500 group-hover:scale-[1.08]">
        <div class="from-ink/30 group-hover:from-ink/40 absolute inset-0 bg-linear-to-t to-transparent transition"></div>
        <span class="absolute inset-0 flex items-center justify-center">
            <span class="flex size-14 items-center justify-center rounded-full bg-teal-700 shadow-xl ring-4 shadow-teal-900/30 ring-white/20 transition group-hover:scale-110 group-hover:bg-teal-800">
                <svg class="ml-0.5 size-5 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </span>
        </span>
        @if ($duration = $video->durationFormatted())
            <span class="absolute right-3 bottom-3 rounded-full bg-black/60 px-2 py-1 font-mono text-xs text-white backdrop-blur-xs">{{ $duration }}</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center gap-3 text-xs">
            @if ($category)
                <span class="font-medium tracking-wider text-teal-700 uppercase">{{ $category->name }}</span>
                <span class="text-ink-muted" aria-hidden="true">·</span>
            @endif
            <time datetime="{{ $video->published_at?->toIso8601String() }}" class="text-ink-muted">
                {{ $video->published_at?->locale('fr')->isoFormat('D MMM YYYY') }}
            </time>
        </div>
        <h3 class="text-ink mt-3 font-serif text-lg leading-snug font-medium transition group-hover:text-teal-700">
            <a href="{{ $url }}" class="before:absolute before:inset-0">{{ $video->title }}</a>
        </h3>
    </div>
</article>
