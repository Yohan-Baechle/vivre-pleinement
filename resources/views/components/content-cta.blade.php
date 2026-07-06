@props(['category' => null])

@php
    $offer = \App\Support\ContentCta::offerFor($category);
@endphp

<section {{ $attributes }} aria-labelledby="content-cta-heading" data-nosnippet>
    <div class="rounded-4xl bg-linear-to-br from-teal-700 to-teal-800 p-8 shadow-xl sm:p-10">
        <p class="text-xs font-medium tracking-wider text-teal-100 uppercase">Pour aller plus loin</p>
        <h2 id="content-cta-heading" class="mt-3 font-serif text-2xl font-medium tracking-tight text-white sm:text-3xl">
            {{ $offer['title'] }}
        </h2>
        <p class="mt-3 max-w-2xl leading-relaxed text-teal-50">
            {{ $offer['description'] }}
        </p>
        <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-4">
            <a href="{{ $offer['url'] }}"
               class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-medium text-teal-800 transition hover:bg-teal-50">
                {{ $offer['label'] }}
                <span aria-hidden="true">→</span>
            </a>
            <a href="{{ route('booking.index') }}"
               class="text-sm font-medium text-teal-100 underline decoration-teal-400/60 underline-offset-4 transition hover:text-white">
                ou réserver une séance individuelle
            </a>
        </div>
    </div>
</section>
