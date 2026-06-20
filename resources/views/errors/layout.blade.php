@extends('layouts.site')

@section('title', ($code ?? 'Erreur').' · '.($title ?? 'Une erreur est survenue').' | Laura Baechlé')

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden pt-24 sm:pt-28">
        {{-- Ciel dégradé, comme le hero de l'accueil --}}
        <div class="to-cream-50 pointer-events-none absolute inset-0 -z-10 bg-linear-to-b from-teal-50 via-sky-50" aria-hidden="true"></div>

        {{-- Nuages flottants animés (réutilise l'anim du hero) --}}
        @include('home.partials.floating-clouds')

        <div class="site-container relative w-full py-16">
            <div class="relative mx-auto max-w-2xl text-center">
                {{-- Numéro d'erreur géant en filigrane, derrière le contenu --}}
                <span
                    class="text-teal-600/20 pointer-events-none absolute -top-12 left-1/2 -z-10 -translate-x-1/2 text-[12rem] leading-none font-bold tracking-tighter select-none sm:-top-20 sm:text-[20rem]"
                    aria-hidden="true"
                >
                    {{ $code ?? '' }}
                </span>

                <h1 class="text-ink relative text-3xl font-semibold tracking-tight sm:text-4xl">
                    <span class="sr-only">Erreur {{ $code ?? '' }}, </span>{{ $title ?? 'Une erreur est survenue' }}
                </h1>

                <p class="text-ink-soft relative mx-auto mt-5 max-w-lg text-base leading-relaxed sm:text-lg">
                    {{ $message ?? "Quelque chose ne s'est pas passé comme prévu. Pas d'inquiétude, on vous remet sur le bon chemin." }}
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-button :href="route('home')" arrow>
                        Retour à l'accueil
                    </x-button>
                    <x-button :href="route('contact')" variant="secondary">
                        Me contacter
                    </x-button>
                </div>

                {{-- Quelques liens utiles --}}
                <div class="mt-12 pt-2">
                    <p class="text-ink-soft text-sm font-medium">Vous cherchiez peut-être&nbsp;:</p>
                    <ul class="text-ink-soft mt-4 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-sm">
                        <li><a href="{{ route('blog.index') }}" class="transition hover:text-teal-700">Le blog</a></li>
                        <li><a href="{{ route('videos.index') }}" class="transition hover:text-teal-700">Les vidéos</a></li>
                        <li><a href="{{ route('book.show') }}" class="transition hover:text-teal-700">Mon livre</a></li>
                        <li><a href="{{ route('booking.index') }}" class="transition hover:text-teal-700">Prendre RDV</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    @include('home.sections.footer')
@endsection
