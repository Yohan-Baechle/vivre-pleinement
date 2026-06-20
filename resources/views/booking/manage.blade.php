@extends('layouts.site')

@php
    $cancelled = $appointment->status === \App\Enums\AppointmentStatus::Cancelled;
    $manageable = $appointment->isManageable();
@endphp

@section('title', 'Gérer mon rendez-vous · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex,nofollow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="to-cream-50 flex min-h-svh flex-col bg-linear-to-b from-teal-100 via-teal-50/60">
        <div class="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
            <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">
                {{ $cancelled ? 'Rendez-vous annulé' : 'Votre rendez-vous' }}
            </h1>

            @if ($cancelled)
                <p class="text-ink-soft mt-4 max-w-md text-base">
                    Ce rendez-vous a été annulé. Vous pouvez en reprendre un quand vous le souhaitez.
                </p>
            @endif

            <div class="ring-ink/5 mt-8 w-full max-w-md rounded-3xl bg-white p-6 text-left shadow-xs ring-1 sm:p-8">
                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Prestation</dt>
                        <dd class="text-ink font-medium">{{ $appointment->service->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Date</dt>
                        <dd class="text-ink @if ($cancelled) @endif font-medium line-through">{{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Heure</dt>
                        <dd class="text-ink @if ($cancelled) @endif font-medium line-through">{{ $appointment->starts_at->format('H:i') }} – {{ $appointment->ends_at->format('H:i') }}</dd>
                    </div>
                    <div class="border-ink/5 flex justify-between gap-4 border-t pt-4">
                        <dt class="text-ink-muted">Référence</dt>
                        <dd class="font-mono text-xs font-medium text-teal-700">{{ $appointment->reference }}</dd>
                    </div>
                </dl>
            </div>

            @if ($manageable)
                <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row">
                    <a href="{{ route('booking.reschedule', $appointment->token) }}"
                       class="inline-flex items-center gap-2 rounded-full bg-teal-700 px-6 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800">
                        Reprogrammer
                        <span aria-hidden="true">→</span>
                    </a>
                    <form method="POST" action="{{ route('booking.cancel', $appointment->token) }}"
                          onsubmit="return confirm('Confirmer l\'annulation de ce rendez-vous ?');">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200 transition hover:bg-rose-50">
                            Annuler le rendez-vous
                        </button>
                    </form>
                </div>
            @elseif (! $cancelled)
                <p class="text-ink-soft mt-8 max-w-md text-sm">
                    Ce rendez-vous est passé ou ne peut plus être modifié. Pour toute question, écrivez-moi.
                </p>
            @endif

            <a href="{{ $cancelled ? route('booking.index') : route('home') }}"
               class="mt-8 inline-flex items-center gap-2 text-sm font-medium text-teal-700 transition hover:text-teal-800">
                <span class="border-b border-teal-700/30 transition hover:border-teal-700">{{ $cancelled ? 'Reprendre rendez-vous' : 'Retour à l\'accueil' }}</span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </main>
@endsection
