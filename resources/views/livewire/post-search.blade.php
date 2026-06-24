<div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12">
    {{-- Bouton drawer mobile --}}
    <div class="mb-2 lg:hidden">
        <button type="button" data-drawer-open
                class="text-ink-soft ring-ink/5 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium ring-1 transition hover:text-teal-700">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 12h18M3 19.5h18"/></svg>
            Catégories &amp; tags
        </button>
    </div>

    {{-- Sidebar : recherche live + catégories/tags (liens indexables) --}}
    <aside class="hidden lg:col-span-3 lg:block">
        <div class="sticky top-28 space-y-8">
            {{-- Recherche live --}}
            <div role="search">
                <label for="post-search" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Rechercher</label>
                <div class="relative mt-2">
                    <span class="text-ink-muted pointer-events-none absolute inset-y-0 left-4 flex items-center">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </span>
                    <input type="search" id="post-search"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Anxiété, phobie..."
                           autocomplete="off"
                           class="text-ink ring-ink/10 placeholder:text-ink-muted w-full rounded-2xl border-0 bg-white py-3 pr-10 pl-11 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                    @if ($search !== '')
                        <button type="button" wire:click="clearSearch"
                                class="text-ink-muted hover:text-ink absolute inset-y-0 right-3 flex items-center transition"
                                aria-label="Effacer la recherche">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>
            </div>

            @include('blog.partials.sidebar', [
                'categories' => $sidebarCategories,
                'popularTags' => $popularTags,
                'filters' => ['category' => $category, 'tag' => $tag, 'q' => $search],
            ])
        </div>
    </aside>

    {{-- Contenu --}}
    <div class="lg:col-span-9">
        {{-- Recherche live mobile (la sidebar desktop a son propre champ) --}}
        <div class="mb-6 lg:hidden" role="search">
            <label for="post-search-mobile" class="sr-only">Rechercher un article</label>
            <div class="relative">
                <span class="text-ink-muted pointer-events-none absolute inset-y-0 left-4 flex items-center">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                </span>
                <input type="search" id="post-search-mobile"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Anxiété, phobie..."
                       autocomplete="off"
                       class="text-ink ring-ink/10 placeholder:text-ink-muted w-full rounded-2xl border-0 bg-white py-3 pr-10 pl-11 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
                @if ($search !== '')
                    <button type="button" wire:click="clearSearch"
                            class="text-ink-muted hover:text-ink absolute inset-y-0 right-3 flex items-center transition"
                            aria-label="Effacer la recherche">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- Article à la une (vue vierge uniquement) --}}
        @if ($featured)
            <section aria-label="Article à la une" class="mb-10 lg:mb-12">
                <p class="inline-flex items-center gap-2 text-xs font-medium tracking-wider text-teal-700 uppercase">
                    <span class="h-px w-8 bg-teal-700"></span>
                    À la une
                </p>
                <div class="mt-4">
                    <x-post-card :post="$featured" featured class="relative" />
                </div>
            </section>
        @endif

        {{-- Compteur + tri --}}
        <div class="flex items-center justify-between gap-3">
            <p class="text-ink-soft text-sm" aria-live="polite">
                {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}
            </p>

            <div class="flex items-center gap-2">
                <label for="sort" class="text-ink-muted text-xs">Trier&nbsp;:</label>
                <select wire:model.live="sort" id="sort" class="text-ink ring-ink/10 rounded-xl border-0 bg-white py-1.5 pr-8 pl-3 text-sm ring-1 focus:ring-2 focus:ring-teal-500">
                    <option value="recent">Plus récents</option>
                    <option value="oldest">Plus anciens</option>
                </select>
            </div>
        </div>

        {{-- Chips des filtres actifs --}}
        @if (count($chips) > 0)
            <div class="mt-3 flex flex-wrap items-center gap-2 lg:mt-4">
                <span class="text-ink-muted text-xs font-medium tracking-wider uppercase">Filtres :</span>
                @foreach ($chips as $chip)
                    <button type="button" wire:click="removeFilter('{{ $chip['key'] }}')"
                            class="group inline-flex items-center gap-1.5 rounded-full bg-teal-700 px-3 py-1 text-xs font-medium text-white transition hover:bg-teal-800">
                        <span>{{ $chip['label'] }}</span>
                        <svg class="size-3 opacity-70 transition group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                @endforeach
                <button type="button" wire:click="clearAll" class="text-ink-muted text-xs underline-offset-4 transition hover:text-teal-700 hover:underline">
                    Tout effacer
                </button>
            </div>
        @endif

        {{-- Grille --}}
        @if ($posts->isNotEmpty())
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" wire:key="post-{{ $post->id }}" class="relative" />
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links('pagination::tailwind') }}
            </div>
        @else
            <div class="border-ink/15 mt-10 rounded-3xl border border-dashed bg-white/60 p-12 text-center">
                <svg class="text-ink-muted mx-auto size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                <p class="text-ink mt-4 font-serif text-xl">Aucun article trouvé.</p>
                <p class="text-ink-soft mt-2 text-sm">Essayez avec d'autres mots-clés ou retirez des filtres.</p>
                <button type="button" wire:click="clearAll" class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-teal-700 hover:text-teal-800">
                    Réinitialiser les filtres
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        @endif
    </div>
</div>
