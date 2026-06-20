@extends('layouts.site')

@section('title', ($title ?? 'Mentions légales').' · Vivre Pleinement')
@section('description', $metaDescription ?? $intro ?? 'Informations légales du site Vivre Pleinement.')

@section('body')
    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-10">
            <x-breadcrumb :items="array_merge([
                ['label' => 'Accueil', 'url' => route('home')],
            ], $breadcrumb ?? [])" />

            <h1 class="text-ink mt-6 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                {{ $title ?? 'Mentions légales' }}
            </h1>
            @if (! empty($intro))
                <p class="text-ink-soft mt-5 text-base sm:text-lg">{{ $intro }}</p>
            @endif
            <p class="text-ink-muted mt-4 text-xs">
                Dernière mise à jour : {{ \Carbon\Carbon::parse(config('legal.last_updated'))->locale('fr')->isoFormat('D MMMM YYYY') }}
            </p>
        </div>
    </header>

    <main class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-10">
            <article class="prose prose-lg prose-ink [&_h2]:text-ink [&_h3]:text-ink max-w-none [&_a]:text-teal-700 [&_a:hover]:text-teal-800 [&_h2]:font-serif [&_h2]:font-medium [&_h2]:tracking-tight [&_h3]:font-serif [&_h3]:font-medium">
                @yield('legal-content')
            </article>
        </div>
    </main>

    @include('home.sections.footer')
@endsection
