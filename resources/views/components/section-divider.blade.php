@props([
    'from' => '#fdfcf9',
    'to' => '#fdfcf9',
    'accent' => '#d6f1f3',
])

<div class="relative" style="background-color: {{ $from }}" aria-hidden="true">
    <svg
        class="block h-16 w-full sm:h-20 lg:h-24"
        viewBox="0 0 1440 120"
        preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            fill="{{ $accent }}"
            opacity="0.55"
            d="M0,64 C240,8 480,8 720,52 C960,96 1200,96 1440,40 L1440,120 L0,120 Z"
        />
        <path
            fill="{{ $to }}"
            d="M0,80 C240,40 480,40 720,68 C960,96 1200,104 1440,72 L1440,120 L0,120 Z"
        />
    </svg>
</div>
