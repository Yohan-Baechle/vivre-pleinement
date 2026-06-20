@props([
    'items' => [],
])

@php
    $items = collect($items)->filter()->values()->all();
    $ldList = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->map(fn ($item, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? null,
        ])->filter(fn ($entry) => $entry['item'] !== null)->all(),
    ];
@endphp

<nav aria-label="Fil d'Ariane" {{ $attributes->class(['flex items-center gap-2 text-xs text-ink-muted']) }}>
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $i => $item)
            <li class="flex items-center gap-2">
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="transition hover:text-teal-700">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page" class="text-ink">{{ $item['label'] }}</span>
                @endif
                @unless ($loop->last)
                    <span aria-hidden="true">/</span>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>

<script type="application/ld+json">{!! json_encode($ldList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
