@php
    $seeds = [
        ['src' => 'seed-1.svg', 'size' => 'w-6 sm:w-8',  'pos' => 'bottom-32 left-[24%]', 'delay' => '0s',   'dur' => '11s'],
        ['src' => 'seed-2.svg', 'size' => 'w-9 sm:w-12', 'pos' => 'bottom-44 left-[34%]', 'delay' => '1.4s', 'dur' => '13s'],
        ['src' => 'seed-3.svg', 'size' => 'w-8 sm:w-10', 'pos' => 'bottom-56 left-[46%]', 'delay' => '0.6s', 'dur' => '12s'],
        ['src' => 'seed-6.svg', 'size' => 'w-8 sm:w-11', 'pos' => 'bottom-28 left-[56%]', 'delay' => '2.1s', 'dur' => '14s'],
        ['src' => 'seed-7.svg', 'size' => 'w-6 sm:w-7',  'pos' => 'bottom-52 left-[66%]', 'delay' => '0.9s', 'dur' => '12.5s'],
        ['src' => 'seed-5.svg', 'size' => 'w-7 sm:w-9',  'pos' => 'bottom-40 left-[76%]', 'delay' => '1.8s', 'dur' => '13.5s'],
        ['src' => 'seed-4.svg', 'size' => 'w-4 sm:w-5',  'pos' => 'bottom-60 left-[84%]', 'delay' => '0.3s', 'dur' => '15s'],
        ['src' => 'seed-8.svg', 'size' => 'w-5 sm:w-6',  'pos' => 'bottom-36 left-[90%]', 'delay' => '2.6s', 'dur' => '11.5s'],
    ];
@endphp

<div class="dandelion pointer-events-none absolute inset-x-0 bottom-0 z-0 h-80 sm:h-96" aria-hidden="true">
    @foreach ($seeds as $seed)
        <img
            src="{{ asset('images/dandelion/'.$seed['src']) }}"
            alt=""
            loading="lazy"
            decoding="async"
            style="animation-delay: {{ $seed['delay'] }}; animation-duration: {{ $seed['dur'] }};"
            class="dandelion-seed absolute hidden sm:block {{ $seed['pos'] }} {{ $seed['size'] }} opacity-55"
        >
    @endforeach

    {{-- Colline sur laquelle les pissenlits sont posés (raccord avec le footer teal-900) --}}
    <svg
        class="absolute inset-x-0 bottom-0 h-24 w-full sm:h-32"
        viewBox="0 0 1440 160"
        preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path fill="#126773" opacity="0.45" d="M0,70 C260,18 520,30 760,66 C1020,104 1240,118 1440,92 L1440,160 L0,160 Z"/>
        <path fill="#135561" d="M0,96 C240,44 480,52 720,82 C1000,116 1240,128 1440,108 L1440,160 L0,160 Z"/>
    </svg>

    <img
        src="{{ asset('images/dandelion/stems.svg') }}"
        alt=""
        width="198"
        height="247"
        loading="lazy"
        decoding="async"
        class="dandelion-stems absolute bottom-8 left-2 w-28 sm:bottom-12 sm:left-8 sm:w-36 lg:w-44"
    >
</div>
