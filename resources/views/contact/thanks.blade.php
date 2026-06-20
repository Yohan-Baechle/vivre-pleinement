@extends('layouts.site')

@section('title', 'Message envoyé – Merci · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="to-cream-50 flex min-h-svh items-center justify-center bg-linear-to-b from-teal-100 via-teal-50/70 px-4 py-32 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-2xl text-center">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-white text-teal-700 shadow-lg ring-1 ring-teal-200">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
            </div>

            <h1 class="text-ink mt-8 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                Message envoyé.<br><span class="text-teal-700 italic">Merci.</span>
            </h1>

            <p class="text-ink-soft mx-auto mt-6 max-w-md text-base sm:text-lg">
                Je reviens vers vous personnellement sous 48h ouvrées (du lundi au vendredi).
                En attendant, vous pouvez explorer le blog ou la vidéo offerte.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('blog.index') }}" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                    Découvrir le blog
                    <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
                <a href="{{ route('home') }}" class="text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700 sm:text-base">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </main>

    @include('home.sections.footer')
@endsection
