@php
    $videos = \App\Models\Video::query()
        ->published()
        ->with('categories')
        ->orderByDesc('published_at')
        ->limit(3)
        ->get();
@endphp

@if ($videos->isNotEmpty())
    <x-section
        id="videos"
        eyebrow="Sur YouTube"
        title="Apprendre en vidéo."
        lead="Conseils et exercices courts pour avancer entre les séances."
        bg="bg-cream-50"
    >
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ($videos as $video)
                <x-video-card :video="$video" />
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('videos.index') }}" class="group text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700">
                <span class="border-b border-transparent transition group-hover:border-teal-700">Voir toutes les vidéos</span>
            </a>
        </div>
    </x-section>
@endif
