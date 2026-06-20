@extends('layouts.site')

@php
    use Carbon\CarbonImmutable;
    $start = CarbonImmutable::parse($appointment->starts_at);
@endphp

@section('title', 'Paiement · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex,nofollow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="bg-cream-50 pt-32 pb-20 sm:pt-36">
        <div class="mx-auto max-w-xl px-4 sm:px-6">
            <h1 class="text-ink text-center font-serif text-3xl font-medium tracking-tight sm:text-4xl">
                Finalisez votre rendez-vous
            </h1>
            <p class="text-ink-soft mt-3 text-center text-sm">
                Paiement sécurisé. Vous ne serez débité·e qu'après confirmation.
            </p>

            {{-- Récapitulatif --}}
            <div class="ring-ink/5 mt-8 rounded-3xl bg-white p-6 shadow-xs ring-1">
                <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">Votre rendez-vous</p>
                <div class="mt-3 flex items-baseline justify-between gap-4">
                    <p class="text-ink font-serif text-xl font-medium">{{ $appointment->service->name }}</p>
                    <p class="text-ink font-serif text-xl font-medium">{{ number_format($appointment->price_cents / 100, 2, ',', ' ') }} €</p>
                </div>
                <p class="text-ink-soft mt-1 text-sm">
                    {{ $start->locale('fr')->isoFormat('dddd D MMMM YYYY à H\hi') }} · {{ $appointment->service->duration_minutes }} min · en visioconférence
                </p>
            </div>

            @php $amountLabel = number_format($appointment->price_cents / 100, 2, ',', ' ').' €'; @endphp

            {{-- Paiement --}}
            <form id="payment-form"
                  class="ring-ink/5 mt-6 rounded-3xl bg-white p-6 shadow-xs ring-1 sm:p-8"
                  data-stripe-key="{{ $stripeKey }}"
                  data-client-secret="{{ $clientSecret }}"
                  data-amount-label="{{ $amountLabel }}"
                  data-return-url="{{ route('booking.confirmation', $appointment->reference) }}">

                <div id="payment-skeleton" class="space-y-4" aria-hidden="true">
                    <div class="bg-cream-100 h-11 animate-pulse rounded-2xl"></div>
                    <div class="bg-cream-100 h-11 animate-pulse rounded-2xl"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-cream-100 h-11 animate-pulse rounded-2xl"></div>
                        <div class="bg-cream-100 h-11 animate-pulse rounded-2xl"></div>
                    </div>
                </div>

                <div id="payment-element" class="min-h-[200px]"></div>

                <p id="payment-error" class="bg-rose-soft/40 text-ink ring-rose-soft mt-4 hidden rounded-2xl px-4 py-3 text-sm ring-1" role="alert"></p>

                <button type="submit" id="payment-submit"
                        class="group mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 disabled:opacity-60">
                    <svg id="payment-submit-spinner" class="hidden size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/>
                    </svg>
                    <span id="payment-submit-label">Payer {{ $amountLabel }}</span>
                </button>

                {{-- Réassurance --}}
                <ul class="text-ink-muted mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs">
                    <li class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        Paiement sécurisé via Stripe
                    </li>
                    <li class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                        Annulation gratuite
                    </li>
                    <li class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                        Lien visio envoyé après paiement
                    </li>
                </ul>
            </form>

            <p class="mt-6 text-center text-sm">
                <a href="{{ route('booking.show', $appointment->service->slug) }}" class="text-ink-muted hover:text-ink underline-offset-2 hover:underline">
                    ← Changer de créneau
                </a>
            </p>
        </div>
    </main>
@endsection

@push('scripts')
    @vite('resources/js/stripe-payment.js')
@endpush
