@extends('layouts.site')

@php
    use App\Enums\AppointmentStatus;
    use App\Enums\PaymentStatus;
    use Carbon\CarbonImmutable;

    $isPaidService = $appointment->price_cents > 0;
    $isConfirmed = $appointment->status === AppointmentStatus::Confirmed;
    $isPending = $appointment->status === AppointmentStatus::Pending;

    $isProcessingPayment = $isPaidService && $appointment->payment_status === PaymentStatus::Unpaid && $isPending;

    if ($isProcessingPayment) {
        $title = 'Paiement reçu !';
        $message = 'Votre paiement a bien été pris en compte. Votre rendez-vous se confirme à l\'instant - vous allez recevoir un email de confirmation dans quelques secondes.';
    } elseif ($isPending) {
        $title = 'Demande bien reçue !';
        $message = 'Votre demande est en attente de confirmation. Je reviens vers vous très vite par email.';
    } else {
        $title = 'Rendez-vous confirmé !';
        $message = 'Tout est bon. Vous allez recevoir un email de confirmation avec tous les détails.';
    }

    $gcalStart = CarbonImmutable::parse($appointment->starts_at)->utc()->format('Ymd\THis\Z');
    $gcalEnd = CarbonImmutable::parse($appointment->ends_at)->utc()->format('Ymd\THis\Z');
    $gcalUrl = 'https://calendar.google.com/calendar/render?'.http_build_query([
        'action' => 'TEMPLATE',
        'text' => 'RDV - '.$appointment->service->name,
        'dates' => $gcalStart.'/'.$gcalEnd,
        'details' => 'Rendez-vous en visioconférence avec Laura Baechlé. Référence : '.$appointment->reference,
    ]);
@endphp

@section('title', 'Rendez-vous confirmé · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex,nofollow">
    @if ($isProcessingPayment)
        <meta http-equiv="refresh" content="6">
    @endif
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="to-cream-50 bg-linear-to-b from-teal-100 via-teal-50/60 pt-32 pb-20 sm:pt-36">
        <div class="mx-auto w-full max-w-4xl px-4 text-center sm:px-6">
            <span @class([
                'flex size-16 items-center justify-center rounded-full shadow-lg mx-auto',
                'bg-teal-700 text-white shadow-teal-700/30' => ! $isProcessingPayment,
                'bg-teal-100 text-teal-700 shadow-teal-700/10' => $isProcessingPayment,
            ])>
                @if ($isProcessingPayment)
                    <svg class="size-8 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/>
                    </svg>
                @else
                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 13 4 4L19 7"/></svg>
                @endif
            </span>

            <h1 class="text-ink mt-8 font-serif text-3xl font-medium tracking-tight sm:text-4xl">{{ $title }}</h1>
            <p class="text-ink-soft mx-auto mt-4 max-w-md text-base">{{ $message }}</p>

            <div class="mt-8 grid grid-cols-1 gap-6 text-left lg:grid-cols-2 lg:items-stretch">
            {{-- Récapitulatif --}}
            <div class="ring-ink/5 w-full rounded-3xl bg-white p-6 shadow-xs ring-1 sm:p-8">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">Votre rendez-vous</p>
                    @if ($isConfirmed && ! $isProcessingPayment)
                        <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700">Confirmé</span>
                    @endif
                </div>

                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Prestation</dt>
                        <dd class="text-ink font-medium">{{ $appointment->service->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Date</dt>
                        <dd class="text-ink font-medium">{{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Heure</dt>
                        <dd class="text-ink font-medium">{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}</dd>
                    </div>
                    @if ($isPaidService)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-muted">Montant</dt>
                            <dd class="text-ink font-medium">{{ number_format($appointment->price_cents / 100, 2, ',', ' ') }} €</dd>
                        </div>
                    @endif
                    @if ($appointment->meeting_url)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-muted">Lien visio</dt>
                            <dd class="truncate font-medium text-teal-700">
                                <a href="{{ $appointment->meeting_url }}" target="_blank" rel="noopener" class="underline-offset-2 hover:underline">Rejoindre</a>
                            </dd>
                        </div>
                    @endif
                    <div class="border-ink/5 flex justify-between gap-4 border-t pt-4">
                        <dt class="text-ink-muted">Référence</dt>
                        <dd class="font-mono text-xs font-medium text-teal-700">{{ $appointment->reference }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Prochaines étapes --}}
            <div class="w-full p-6 sm:p-8">
                <p class="text-ink-muted text-xs font-medium tracking-wider uppercase">Et maintenant ?</p>
                <ul class="text-ink-soft mt-4 space-y-4 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-teal-50 font-serif text-lg font-medium text-teal-700">1</span>
                        Vous recevez un email de confirmation récapitulatif.
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-teal-50 font-serif text-lg font-medium text-teal-700">2</span>
                        Le lien de visioconférence vous est transmis avant le rendez-vous.
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-teal-50 font-serif text-lg font-medium text-teal-700">3</span>
                        Un imprévu ? Vous pouvez annuler ou reprogrammer à tout moment.
                    </li>
                </ul>
            </div>
            </div>

            {{-- Actions agenda --}}
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $gcalUrl }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    Ajouter à Google Agenda
                </a>
                <a href="{{ route('booking.ics', $appointment->reference) }}"
                   class="text-ink ring-ink/10 hover:bg-cream-50 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-medium ring-1 transition">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Télécharger (.ics)
                </a>
            </div>

            {{-- Gérer / retour --}}
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm">
                @if ($appointment->token)
                    <a href="{{ route('booking.manage', $appointment->token) }}" class="font-medium text-teal-700 underline-offset-2 hover:underline">
                        Gérer mon rendez-vous
                    </a>
                @endif
                <a href="{{ route('home') }}" class="text-ink-soft inline-flex items-center gap-2 font-medium transition hover:text-teal-700">
                    <span class="border-b border-transparent transition hover:border-teal-700">Retour à l'accueil</span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </main>
@endsection
