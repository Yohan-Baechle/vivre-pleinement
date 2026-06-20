@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'lg',
    'arrow' => false,
])

@php
    $sizes = [
        'sm' => 'px-5 py-2.5 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-7 py-3.5 text-sm sm:text-base',
    ];

    $variants = [
        'primary' => 'bg-teal-700 text-white hover:bg-teal-800 '.($size === 'lg' ? 'shadow-lg shadow-teal-700/20' : 'shadow'),
        'secondary' => 'bg-white text-ink-soft ring-1 ring-ink/15 hover:bg-cream-100 hover:text-ink',
    ];

    $classes = [
        'group inline-flex items-center justify-center gap-2 rounded-full font-medium transition',
        $sizes[$size] ?? $sizes['lg'],
        $variants[$variant] ?? $variants['primary'],
    ];
@endphp

<{{ $href ? 'a' : 'button' }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class($classes) }}
>
    {{ $slot }}
    @if ($arrow)<span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>@endif
</{{ $href ? 'a' : 'button' }}>
