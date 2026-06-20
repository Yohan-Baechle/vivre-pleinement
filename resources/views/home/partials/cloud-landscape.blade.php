@php
    $cloudLayers = [
        1 => ['z' => 80, 'speed' => 0.45, 'zoom' => 0.00036, 'h' => 'h-16 sm:h-28 lg:h-56',      'fill' => '#fdfcf9'],
        2 => ['z' => 70, 'speed' => 0.36, 'zoom' => 0.00028, 'h' => 'h-20 sm:h-36 lg:h-80',      'fill' => '#fdfcf9'],
        3 => ['z' => 60, 'speed' => 0.28, 'zoom' => 0.00021, 'h' => 'h-24 sm:h-40 lg:h-88',      'fill' => '#f4fcfc'],
        4 => ['z' => 50, 'speed' => 0.21, 'zoom' => 0.00015, 'h' => 'h-28 sm:h-44 lg:h-[25rem]', 'fill' => '#effafb'],
        5 => ['z' => 40, 'speed' => 0.15, 'zoom' => 0.00010, 'h' => 'h-32 sm:h-48 lg:h-[26rem]', 'fill' => '#e2f5f7', 'bottom' => '-bottom-12'],
        6 => ['z' => 30, 'speed' => 0.10, 'zoom' => 0.00006, 'h' => 'h-36 sm:h-56 lg:h-[30rem]', 'fill' => '#d6f1f3', 'bottom' => '-bottom-12'],
        7 => ['z' => 20, 'speed' => 0.06, 'zoom' => 0.00003, 'h' => 'h-40 sm:h-64 lg:h-[32rem]', 'fill' => '#c2eaed'],
        8 => ['z' => 10, 'speed' => 0.03, 'zoom' => 0.00000, 'h' => 'h-44 sm:h-72 lg:h-[34rem]', 'fill' => '#b1e3e7', 'bottom' => '-bottom-12'],
    ];
@endphp

<div class="to-cream-50 pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-screen bg-linear-to-b from-teal-50 via-sky-50" aria-hidden="true"></div>

<div class="pointer-events-none absolute inset-x-0 bottom-0 select-none" aria-hidden="true">
    @foreach ($cloudLayers as $n => $layer)
        <div
            data-parallax="{{ $layer['speed'] }}"
            data-zoom="{{ $layer['zoom'] }}"
            style="z-index: {{ $layer['z'] }}"
            class="absolute inset-x-0 {{ $layer['bottom'] ?? 'bottom-0' }} origin-bottom {{ $layer['h'] }} will-change-transform"
        >
            <img
                src="{{ asset("images/clouds/cloud-{$n}.svg") }}"
                alt=""
                class="size-full"
            >
            <div class="absolute inset-x-0 top-full h-screen w-full" style="background-color: {{ $layer['fill'] }}"></div>
        </div>
    @endforeach
</div>
