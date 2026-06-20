@props([
    'post',
    'featured' => false,
])

@php
    $media = $post->getFirstMedia('featured');
    $category = $post->categories->first();
    $url = route('blog.show', $post);
@endphp

<article {{ $attributes->class([
    'group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md hover:shadow-teal-700/5 hover:ring-teal-200/60',
    'lg:flex-row lg:items-stretch' => $featured,
]) }}>
    <a href="{{ $url }}" @class([
        'block aspect-[16/10] overflow-hidden bg-linear-to-br from-teal-100 via-cream-100 to-rose-soft/40',
        'lg:aspect-auto lg:w-1/2' => $featured,
    ]) aria-label="Lire {{ $post->title }}">
        @if ($media)
            <x-responsive-image
                :media="$media"
                :alt="$post->title"
                sizes="(min-width: 1024px) 600px, 100vw"
                class="size-full object-cover transition duration-500 group-hover:scale-105" />
        @else
            <div class="flex size-full items-center justify-center">
                <svg class="size-16 text-teal-700/30" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                    <polygon points="10,55 50,30 90,55 50,50"/>
                    <polygon points="50,30 50,50 70,75 55,55" opacity="0.75"/>
                    <polygon points="50,30 50,50 30,75 45,55" opacity="0.6"/>
                </svg>
            </div>
        @endif
    </a>

    <div @class(['flex flex-1 flex-col p-6', 'lg:p-8' => $featured])>
        <div class="flex flex-wrap items-center gap-3 text-xs">
            @if ($category)
                <a href="{{ route('blog.category', $category->slug) }}"
                   class="font-medium tracking-wider text-teal-700 uppercase transition hover:text-teal-800">
                    {{ $category->name }}
                </a>
                <span class="text-ink-muted" aria-hidden="true">·</span>
            @endif
            <time datetime="{{ $post->published_at?->toIso8601String() }}" class="text-ink-muted">
                {{ $post->published_at?->locale('fr')->isoFormat('D MMM YYYY') }}
            </time>
            <span class="text-ink-muted" aria-hidden="true">·</span>
            <span class="text-ink-muted">{{ $post->readingTimeMinutes() }} min de lecture</span>
        </div>

        <h2 @class([
            'mt-3 font-serif font-medium text-ink leading-snug transition group-hover:text-teal-700',
            'text-xl' => ! $featured,
            'text-2xl lg:text-3xl' => $featured,
        ])>
            <a href="{{ $url }}" class="before:absolute before:inset-0">{{ $post->title }}</a>
        </h2>

        @if ($post->excerpt)
            <p class="text-ink-soft mt-3 line-clamp-3 flex-1 text-sm leading-relaxed">{{ $post->cleanExcerpt() }}</p>
        @endif

        <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-teal-700">
            Lire l'article
            <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
        </span>
    </div>
</article>
