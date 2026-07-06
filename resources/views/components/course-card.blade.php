@props([
    'course',
])

@php
    use Illuminate\Support\Number;

    $media = $course->getFirstMedia('cover');
    $url = route('courses.show', $course);
@endphp

<article {{ $attributes->class([
    'group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-ink/5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md hover:shadow-teal-700/5 hover:ring-teal-200/60',
]) }}>
    <a href="{{ $url }}" class="block aspect-[16/10] overflow-hidden bg-linear-to-br from-teal-100 via-cream-100 to-rose-soft/40" aria-label="Découvrir {{ $course->title }}">
        @if ($media)
            <x-responsive-image
                :media="$media"
                :alt="$course->title"
                sizes="(min-width: 1024px) 400px, 100vw"
                class="size-full object-cover transition duration-500 group-hover:scale-105" />
        @else
            <div class="flex size-full items-center justify-center">
                <svg class="size-16 text-teal-700/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-6">
        @if ($course->level)
            <span class="text-ink-muted text-xs font-medium tracking-wider uppercase">{{ $course->level }}</span>
        @endif

        <h2 class="text-ink mt-2 font-serif text-xl font-medium leading-snug transition group-hover:text-teal-700">
            <a href="{{ $url }}" class="before:absolute before:inset-0">{{ $course->title }}</a>
        </h2>

        @if ($course->subtitle)
            <p class="text-ink-soft mt-3 line-clamp-3 flex-1 text-sm leading-relaxed">{{ $course->subtitle }}</p>
        @endif

        <div class="mt-5 flex items-center justify-between">
            <span class="text-ink font-serif text-lg font-medium">
                {{ Number::currency($course->price, in: 'EUR', locale: 'fr') }}
            </span>
            <span class="inline-flex items-center gap-1.5 text-sm font-medium text-teal-700">
                Découvrir
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </span>
        </div>
    </div>
</article>
