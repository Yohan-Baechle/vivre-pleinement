@extends('layouts.site')

@section('title', 'Réserver votre heure de coaching · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Choisissez le créneau de l'heure de coaching incluse dans votre commande.">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                    {{ $service->duration_minutes }} min · déjà réglé avec votre commande
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                    Choisissez votre créneau
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Bonjour {{ $order->customer_first_name }}, voici l'heure de coaching incluse dans votre
                    formule. Rien à payer : choisissez simplement le moment qui vous convient.
                </p>
            </div>
        </div>
    </header>

    <main class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-10">
            @livewire('booking-calendar', ['service' => $service, 'bookOrderToken' => $order->token])
        </div>
    </main>

    @include('home.sections.footer')
@endsection
