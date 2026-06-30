<div>
    {{-- Barre de recherche --}}
    <div class="mb-8">
        <label for="video-search" class="sr-only">Rechercher une vidéo</label>
        <div class="relative max-w-xl">
            <svg class="text-ink-muted pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
            </svg>
            <input
                type="search"
                id="video-search"
                wire:model.live.debounce.300ms="search"
                placeholder="Anxiété, phobie, sommeil..."
                autocomplete="off"
                class="border-ink/10 text-ink placeholder:text-ink-muted w-full rounded-full border bg-white py-3 pr-12 pl-12 text-sm shadow-sm transition focus:border-teal-300 focus:ring-2 focus:ring-teal-200 focus:outline-none"
            >
            @if ($search !== '')
                <button
                    type="button"
                    wire:click="clearSearch"
                    class="text-ink-muted hover:text-ink absolute top-1/2 right-4 -translate-y-1/2 transition"
                    aria-label="Effacer la recherche"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Filtres par catégorie --}}
    @if ($this->categories->isNotEmpty())
        <nav class="mb-10 flex flex-wrap items-center gap-2" aria-label="Filtres par catégorie">
            <button
                type="button"
                wire:click="selectCategory('')"
                @class([
                    'inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition',
                    'bg-teal-700 text-white shadow shadow-teal-700/20' => $category === '',
                    'bg-white text-ink-soft ring-1 ring-ink/5 hover:text-teal-700' => $category !== '',
                ])
            >
                Toutes ({{ $this->totalVideos }})
            </button>
            @foreach ($this->categories as $cat)
                <button
                    type="button"
                    wire:click="selectCategory('{{ $cat->slug }}')"
                    @class([
                        'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition',
                        'bg-teal-700 text-white shadow shadow-teal-700/20' => $category === $cat->slug,
                        'bg-white text-ink-soft ring-1 ring-ink/5 hover:text-teal-700' => $category !== $cat->slug,
                    ])
                >
                    {{ $cat->name }}
                    <span @class([
                        'rounded-full px-1.5 text-xs',
                        'bg-white/20' => $category === $cat->slug,
                        'bg-cream-200 text-ink-muted' => $category !== $cat->slug,
                    ])>{{ $cat->videos_count }}</span>
                </button>
            @endforeach
        </nav>
    @endif

    {{-- Résultats --}}
    @if ($videos->isNotEmpty())
        <h2 class="sr-only">Liste des vidéos</h2>

        @if ($hasSearch)
            <p class="text-ink-soft mb-6 text-sm" aria-live="polite">
                {{ $videos->total() }} {{ \Illuminate\Support\Str::plural('résultat', $videos->total()) }} pour « {{ $search }} »
            </p>
        @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($videos as $video)
                <x-video-card :video="$video" wire:key="video-{{ $video->id }}" />
            @endforeach
        </div>

        <div class="mt-12">
            {{ $videos->links('pagination::tailwind') }}
        </div>
    @else
        <div class="border-ink/15 rounded-3xl border border-dashed bg-white/60 p-12 text-center">
            <svg class="text-ink-muted mx-auto size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="7"/>
                <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
            </svg>
            <p class="text-ink mt-4 font-serif text-xl">
                @if ($hasSearch)
                    Aucune vidéo pour « {{ $search }} ».
                @else
                    Aucune vidéo pour l'instant.
                @endif
            </p>
            <p class="text-ink-soft mt-2 text-sm">
                @if ($hasSearch)
                    Essayez un autre mot-clé ou <button type="button" wire:click="clearSearch" class="text-teal-700 underline">réinitialisez la recherche</button>.
                @else
                    Les vidéos arriveront bientôt - revenez nous voir !
                @endif
            </p>
        </div>
    @endif
</div>
