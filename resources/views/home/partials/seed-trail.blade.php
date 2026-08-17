@php
    // Graines disséminées sur toute la hauteur de la page. Leur transform est
    // pilotée par le scroll (voir seed-wind.js) : elles entrent plus grosses
    // par le bas, montent en rétrécissant et en dérivant, comme emportées au loin.
    // drift = amplitude latérale (px), spin = rotation max (deg), depth = vitesse.
    $seeds = [
        ['src' => 'seed-3.svg', 'size' => 'w-12 sm:w-16', 'pos' => 'top-[16%] left-[5%]',   'op' => 'opacity-45', 'drift' => 40,  'spin' => 14, 'depth' => 1.15],
        ['src' => 'seed-7.svg', 'size' => 'w-10 sm:w-12', 'pos' => 'top-[24%] left-[42%]',  'op' => 'opacity-30', 'drift' => -30, 'spin' => -10, 'depth' => 0.85],
        ['src' => 'seed-5.svg', 'size' => 'w-12 sm:w-14', 'pos' => 'top-[38%] right-[6%]',  'op' => 'opacity-45', 'drift' => -46, 'spin' => -16, 'depth' => 1.25],
        ['src' => 'seed-4.svg', 'size' => 'w-8 sm:w-10',  'pos' => 'top-[47%] left-[8%]',   'op' => 'opacity-30', 'drift' => 34,  'spin' => 12, 'depth' => 0.9],
        ['src' => 'seed-8.svg', 'size' => 'w-10 sm:w-12', 'pos' => 'top-[58%] left-[48%]',  'op' => 'opacity-30', 'drift' => 26,  'spin' => 8,  'depth' => 1.0],
        ['src' => 'seed-2.svg', 'size' => 'w-14 sm:w-20', 'pos' => 'top-[68%] right-[7%]',  'op' => 'opacity-45', 'drift' => -52, 'spin' => -18, 'depth' => 1.3],
        ['src' => 'seed-6.svg', 'size' => 'w-10 sm:w-12', 'pos' => 'top-[80%] left-[6%]',   'op' => 'opacity-35', 'drift' => 40,  'spin' => 14, 'depth' => 1.1],
        ['src' => 'seed-1.svg', 'size' => 'w-9 sm:w-11',  'pos' => 'top-[88%] right-[40%]', 'op' => 'opacity-25', 'drift' => -24, 'spin' => -9, 'depth' => 0.8],
    ];
@endphp

<div class="pointer-events-none absolute inset-0 z-20 overflow-hidden" aria-hidden="true">
    @foreach ($seeds as $seed)
        <img
            src="{{ asset('images/dandelion/'.$seed['src']) }}"
            alt=""
            loading="lazy"
            decoding="async"
            data-seed
            data-seed-drift="{{ $seed['drift'] }}"
            data-seed-spin="{{ $seed['spin'] }}"
            data-seed-depth="{{ $seed['depth'] }}"
            class="dandelion-seed-wind absolute {{ $seed['pos'] }} {{ $seed['size'] }} {{ $seed['op'] }} will-change-transform"
        >
    @endforeach
</div>
