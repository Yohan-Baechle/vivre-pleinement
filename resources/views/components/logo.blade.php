@props(['class' => 'h-12 w-auto'])

<img
    src="{{ asset('images/logo@1x.webp') }}"
    srcset="{{ asset('images/logo@1x.webp') }} 1x, {{ asset('images/logo@2x.webp') }} 2x, {{ asset('images/logo@4x.webp') }} 4x"
    alt="Laura Baechlé"
    width="248"
    height="96"
    {{ $attributes->merge(['class' => $class]) }}
>
