@props([
    'href',
    'label',
    'eyebrow' => 'Ressource recommandée',
    'cta' => 'Découvrir',
    'icon' => 'sparkles',
])

@php
    $icons = [
        'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
        'headphones' => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5a9 9 0 0 1 18 0v5a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
        'leaf' => '<path d="M11 20A7 7 0 0 1 4 13c0-6 8-9 16-9 0 8-3 16-9 16z"/><path d="M4 20c2-5 6-9 12-11"/>',
        'sparkles' => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/>',
    ];
    $iconPath = $icons[$icon] ?? $icons['sparkles'];
@endphp

<div class="not-prose to-cream-50 relative mt-12 mb-8 rounded-3xl border border-teal-700/10 bg-linear-to-br from-teal-50/80 shadow-sm">
    <div class="ring-cream-50 absolute -top-6 left-1/2 flex size-14 -translate-x-1/2 items-center justify-center rounded-2xl bg-teal-700 text-white shadow-lg ring-4 shadow-teal-700/25 sm:left-8 sm:translate-x-0">
        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            {!! $iconPath !!}
        </svg>
    </div>

    <div class="flex flex-col gap-4 px-5 pt-12 pb-5 sm:flex-row sm:items-center sm:gap-6 sm:px-6 sm:py-6 sm:pl-28">
        <div class="min-w-0 flex-1 text-center sm:text-left">
            <p class="text-xs font-semibold tracking-wider text-teal-700 uppercase">{{ $eyebrow }}</p>
            <p class="text-ink mt-1 font-serif text-lg leading-snug font-medium">{{ $label }}</p>
        </div>

        <div class="shrink-0">
            <a href="{{ $href }}" target="_blank" rel="nofollow sponsored noopener"
               class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:w-auto">
                {{ $cta }}
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </a>
            <p class="text-ink-muted mt-2 text-center text-[0.7rem] sm:text-right">Lien partenaire</p>
        </div>
    </div>
</div>
