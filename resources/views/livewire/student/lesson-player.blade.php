<div>
    {{-- Lecteur vidéo --}}
    @if ($embed = $lesson->embedUrl())
        <div class="aspect-video w-full overflow-hidden rounded-3xl bg-ink shadow-lg">
            <iframe
                src="{{ $embed }}"
                title="{{ $lesson->title }}"
                class="size-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                loading="lazy"></iframe>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-ink font-serif text-2xl font-medium sm:text-3xl">{{ $lesson->title }}</h1>

        @if ($completed)
            <button type="button" wire:click="markIncomplete"
                    class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-2 text-sm font-medium text-teal-700 ring-1 ring-teal-200 transition hover:bg-teal-100">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                Leçon terminée
            </button>
        @else
            <x-button wire:click="markComplete" size="md">Marquer comme terminé</x-button>
        @endif
    </div>

    @if ($lesson->content)
        <div class="prose prose-teal mt-8 max-w-none">
            {!! $lesson->content !!}
        </div>
    @endif

    {{-- Navigation précédent / suivant --}}
    <div class="border-ink/5 mt-10 flex items-center justify-between border-t pt-6">
        @if ($previous)
            <a href="{{ route('student.lesson', [$course, $previous]) }}" class="text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700">
                <span aria-hidden="true">←</span> Leçon précédente
            </a>
        @else
            <span></span>
        @endif

        @if ($next)
            <a href="{{ route('student.lesson', [$course, $next]) }}" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                Leçon suivante <span aria-hidden="true">→</span>
            </a>
        @else
            <a href="{{ route('student.course', $course) }}" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                Retour au programme <span aria-hidden="true">→</span>
            </a>
        @endif
    </div>
</div>
