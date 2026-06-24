@php
    use App\Support\BlogFilters;
    $filters ??= [];
    $categories ??= collect();
    $popularTags ??= collect();
    $activeCategory = $filters['category'] ?? null;
    $activeTag = $filters['tag'] ?? null;
@endphp

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
