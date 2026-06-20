@props([
    'surface' => 'teal',
])

@php
    $surfaces = [
        'teal' => 'bg-teal-50',
        'glass' => 'bg-white/80',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200',
    $surfaces[$surface] ?? $surfaces['teal'],
]) }}>
    {{ $slot }}
</span>
