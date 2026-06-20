@php
    use App\Support\BlogFilters;
    $filters ??= [];
    $categories ??= collect();
    $popularTags ??= collect();
    $sidebarId ??= 'sidebar-'.\Illuminate\Support\Str::random(6);
    $searchId = $sidebarId.'-q';
    $activeQ = $filters['q'] ?? null;
    $activeCategory = $filters['category'] ?? null;
    $activeTag = $filters['tag'] ?? null;
@endphp

<form method="GET" action="{{ route('blog.index') }}" role="search">
    <label for="{{ $searchId }}" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Rechercher</label>
    <div class="relative mt-2">
        <span class="text-ink-muted pointer-events-none absolute inset-y-0 left-4 flex items-center">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
        </span>
        <input type="search" id="{{ $searchId }}" name="q" value="{{ $activeQ }}" placeholder="Anxiété, phobie..."
               class="text-ink ring-ink/10 placeholder:text-ink-muted w-full rounded-2xl border-0 bg-white py-3 pr-10 pl-11 text-sm ring-1 focus:ring-2 focus:ring-teal-500 focus:outline-hidden">
        @if ($activeQ)
            <a href="{{ BlogFilters::url('blog.index', $filters, ['q' => null]) }}"
               class="text-ink-muted hover:text-ink absolute inset-y-0 right-3 flex items-center transition"
               aria-label="Effacer la recherche">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </a>
        @endif
        @foreach (['category', 'tag', 'sort'] as $field)
            @if (! empty($filters[$field]))
                <input type="hidden" name="{{ $field }}" value="{{ $filters[$field] }}">
            @endif
        @endforeach
        <button type="submit" class="sr-only">Rechercher</button>
    </div>
    <p class="text-ink-muted mt-2 text-xs">Appuyez sur Entrée pour rechercher</p>
</form>

<div>
    <h2 class="text-ink-muted text-xs font-medium tracking-wider uppercase">Catégories</h2>
    <ul class="mt-3 space-y-1">
        <li>
            <a href="{{ BlogFilters::url('blog.index', $filters, ['category' => null]) }}"
               @class([
                   'flex items-center justify-between rounded-xl px-3 py-2 text-sm transition',
                   'bg-teal-700 text-white shadow shadow-teal-700/20' => ! $activeCategory,
                   'text-ink-soft hover:bg-white hover:text-teal-700' => $activeCategory,
               ])>
                <span>Toutes</span>
            </a>
        </li>
        @foreach ($categories as $category)
            @php($isActive = $activeCategory === $category->slug)
            <li>
                <a href="{{ BlogFilters::url('blog.index', $filters, ['category' => $category->slug]) }}"
                   @class([
                       'flex items-center justify-between rounded-xl px-3 py-2 text-sm transition',
                       'bg-teal-700 text-white shadow shadow-teal-700/20' => $isActive,
                       'text-ink-soft hover:bg-white hover:text-teal-700' => ! $isActive,
                   ])>
                    <span>{{ $category->name }}</span>
                    <span @class([
                        'rounded-full px-2 py-0.5 text-xs',
                        'bg-white/20 text-white' => $isActive,
                        'bg-cream-200 text-ink-muted' => ! $isActive,
                    ])>{{ $category->posts_count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>

@if ($popularTags->isNotEmpty())
    <div>
        <h2 class="text-ink-muted text-xs font-medium tracking-wider uppercase">Tags populaires</h2>
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach ($popularTags as $tag)
                @php($isActive = $activeTag === $tag->slug)
                <li>
                    <a href="{{ BlogFilters::url('blog.index', $filters, ['tag' => $tag->slug]) }}"
                       @class([
                           'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition',
                           'bg-teal-700 text-white shadow shadow-teal-700/20' => $isActive,
                           'bg-white text-ink-soft ring-1 ring-ink/5 hover:text-teal-700' => ! $isActive,
                       ])>
                        #{{ $tag->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <h2 class="text-ink-muted text-xs font-medium tracking-wider uppercase">S'abonner</h2>
    <a href="{{ route('blog.rss') }}" class="text-ink-soft mt-3 inline-flex items-center gap-2 text-sm transition hover:text-teal-700">
        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M6.18 15.64a2.18 2.18 0 1 1 0 4.36 2.18 2.18 0 0 1 0-4.36zM4 4.44A19.56 19.56 0 0 1 19.56 20h-2.83A16.73 16.73 0 0 0 4 7.27V4.44zm0 5.66a13.9 13.9 0 0 1 9.9 9.9h-2.83A11.07 11.07 0 0 0 4 12.93V10.1z"/>
        </svg>
        Flux RSS
    </a>
</div>
