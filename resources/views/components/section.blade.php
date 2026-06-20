@props([
    'id' => null,
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'bg' => 'bg-cream-50',
    'headerWidth' => 'max-w-2xl',
])

<section @if($id) id="{{ $id }}" @endif {{ $attributes->class([$bg, 'relative py-20 sm:py-24 lg:py-32']) }}>
    <div class="site-container">
        @if($eyebrow || $title || $lead)
            <div class="mx-auto {{ $headerWidth }} text-center">
                @if($eyebrow)
                    <p class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                        <span class="size-1.5 rounded-full bg-teal-500"></span>
                        {{ $eyebrow }}
                    </p>
                @endif
                @if($title)
                    <h2 class="text-ink mt-5 font-serif text-3xl font-medium tracking-tight sm:text-4xl lg:text-5xl">
                        {{ $title }}
                    </h2>
                @endif
                @if($lead)
                    <p class="text-ink-soft mt-5 text-base sm:text-lg">
                        {{ $lead }}
                    </p>
                @endif
            </div>
        @endif

        <div @class(['mt-12 sm:mt-16' => $eyebrow || $title || $lead])>
            {{ $slot }}
        </div>
    </div>
</section>
