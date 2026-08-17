@props([])

@php
    $socials = \App\Support\SiteContact::socials();
@endphp

<section {{ $attributes }} aria-labelledby="author-card-heading" data-nosnippet>
    <div class="border-ink/10 flex flex-col gap-5 border-t pt-8 sm:flex-row sm:items-start sm:gap-6">
        <img src="{{ asset('images/laura-portrait-400.webp') }}"
             alt="Laura Baechlé, praticienne ACT"
             width="80" height="80" loading="lazy"
             class="ring-cream-100 size-20 shrink-0 rounded-full object-cover ring-4">
        <div class="min-w-0">
            <p class="text-ink-muted text-xs font-medium tracking-wider uppercase">Écrit par</p>
            <p id="author-card-heading" class="text-ink mt-1 font-serif text-xl font-medium">Laura Baechlé</p>
            <p class="mt-0.5 text-sm font-medium text-teal-700">Praticienne ACT en accompagnement des troubles anxieux</p>
            <p class="text-ink-soft mt-3 text-sm leading-relaxed">
                J'ai moi-même souffert de troubles anxieux pendant des années, avant de les apprivoiser grâce à la thérapie ACT, que j'exerce aujourd'hui. Sur ce site, je partage les outils qui m'ont permis de vivre pleinement.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">
                <a href="{{ route('about') }}" class="text-sm font-medium text-teal-700 transition hover:text-teal-800">
                    Découvrir mon parcours <span aria-hidden="true">→</span>
                </a>
                @foreach ($socials as $name => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                       class="text-ink-muted text-sm underline decoration-teal-700/30 underline-offset-4 transition hover:text-teal-700">
                        {{ $name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
