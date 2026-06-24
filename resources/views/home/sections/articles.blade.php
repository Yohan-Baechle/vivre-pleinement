@php
    $articles = \App\Models\Post::query()
        ->published()
        ->with(['categories', 'media'])
        ->orderByDesc('published_at')
        ->limit(3)
        ->get();
@endphp

@if ($articles->isNotEmpty())
    <x-section
        id="blog"
        eyebrow="Le blog"
        title="Lire pour mieux comprendre."
        lead="Des articles fouillés pour comprendre l'anxiété et avancer entre les séances."
        bg="bg-cream-50"
    >
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ($articles as $post)
                <x-post-card :post="$post" class="relative" />
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('blog.index') }}" class="group text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700">
                <span class="border-b border-transparent transition group-hover:border-teal-700">Voir tous les articles</span>
            </a>
        </div>
    </x-section>
@endif
