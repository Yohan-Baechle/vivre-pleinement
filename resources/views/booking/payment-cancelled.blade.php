@extends('layouts.site')

@section('title', 'Paiement annulé · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex,nofollow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="from-cream-50 to-cream-50 flex min-h-svh flex-col bg-linear-to-b">
        <div class="mx-auto flex w-full max-w-xl flex-1 flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
            <span class="bg-cream-100 text-ink-soft ring-ink/10 flex size-16 items-center justify-center rounded-full ring-1">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            </span>

            <h1 class="text-ink mt-8 font-serif text-3xl font-medium tracking-tight">Paiement annulé</h1>
            <p class="text-ink-soft mt-4 max-w-md text-base">
                Votre rendez-vous n'a pas été confirmé car le paiement n'a pas abouti. Vous pouvez réessayer quand vous le souhaitez - le créneau reste disponible quelques instants.
            </p>

            <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row">
                <a href="{{ route('booking.show', $appointment->service->slug) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-teal-700 px-6 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800">
                    Réessayer
                    <span aria-hidden="true">→</span>
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                    <span class="border-b border-teal-700/30 transition hover:border-teal-700">Retour à l'accueil</span>
                </a>
            </div>
        </div>
    </main>
@endsection
