@extends('layouts.site')

@section('title', $service->name.' · Prendre rendez-vous – Vivre Pleinement')

@push('head')
    <meta name="description" content="Réservez « {{ $service->name }} » avec Laura Baechlé : choisissez votre créneau en ligne, en visioconférence.">
    <link rel="canonical" href="{{ route('booking.show', $service->slug) }}">
    <meta name="robots" content="noindex,follow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Prendre rendez-vous', 'url' => route('booking.index')],
                ['label' => $service->name],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    {{ $service->duration_minutes }} min · {{ $service->isFree() ? 'Gratuit' : number_format($service->price, 2, ',', ' ').' €' }}
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                    {{ $service->name }}
                </h1>
                @if ($service->description)
                    <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">{{ $service->description }}</p>
                @endif
            </div>
        </div>
    </header>

    <main class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-10">
            @livewire('booking-calendar', ['service' => $service])
        </div>
    </main>

    @include('home.sections.footer')
@endsection
