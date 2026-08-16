@extends('layouts.site')

@php
    use App\Enums\BookOrderStatus;
    use Illuminate\Support\Number;

    $isProcessing = $order->status === BookOrderStatus::Pending;
    $isRefunded = $order->status === BookOrderStatus::Refunded;
    $hasFile = $order->product->isDeliverable();
@endphp

@section('title', 'Merci · '.$order->product->name)

@push('head')
    <meta name="robots" content="noindex,nofollow">
    @if ($isProcessing)
        <meta http-equiv="refresh" content="6">
    @endif
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="bg-cream-50 pt-32 pb-20 sm:pt-36">
        <div class="mx-auto max-w-xl px-4 sm:px-6">
            <div class="ring-ink/5 rounded-4xl bg-white p-8 text-center shadow-xs ring-1 sm:p-10">
                @if ($isRefunded)
                    <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">
                        Commande remboursée
                    </h1>
                    <p class="text-ink-soft mt-4 text-sm sm:text-base">
                        Cette commande a été remboursée, le téléchargement n'est plus accessible.
                        Une question ? Écrivez-moi, je vous réponds.
                    </p>
                @elseif ($isProcessing)
                    <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">
                        Paiement reçu !
                    </h1>
                    <p class="text-ink-soft mt-4 text-sm sm:text-base">
                        Votre paiement a bien été pris en compte. Votre commande se finalise à l'instant —
                        vous allez recevoir votre lien de téléchargement par email dans quelques secondes.
                    </p>
                @else
                    <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">
                        Merci, votre livre vous attend.
                    </h1>
                    <p class="text-ink-soft mt-4 text-sm sm:text-base">
                        Un email vient de partir vers <strong>{{ $order->customer_email }}</strong> avec votre lien
                        de téléchargement. Vous pouvez aussi le récupérer tout de suite.
                    </p>

                    @if (session('status'))
                        <p class="bg-cream-100 text-ink-soft mt-6 rounded-2xl px-5 py-3 text-sm" role="status">
                            {{ session('status') }}
                        </p>
                    @endif

                    @if ($hasFile)
                        <a href="{{ $order->downloadUrl() }}"
                           class="group mt-8 inline-flex items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800">
                            Télécharger le livre
                            <span class="transition group-hover:translate-y-0.5" aria-hidden="true">↓</span>
                        </a>
                    @else
                        <p class="bg-cream-100 text-ink-soft mt-8 rounded-2xl px-5 py-4 text-sm">
                            Votre fichier est en cours de préparation : je vous l'envoie par email très vite.
                        </p>
                    @endif

                    @if ($order->canBookCoaching())
                        <div class="border-ink/5 mt-8 border-t pt-8">
                            <p class="text-ink font-medium">Il reste votre heure de coaching.</p>
                            <p class="text-ink-soft mt-2 text-sm">
                                Choisissez le créneau qui vous arrange, c'est déjà réglé.
                            </p>
                            <a href="{{ route('book.coaching', $order->token) }}"
                               class="text-ink hover:bg-cream-50 ring-ink/10 mt-4 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-medium ring-1 transition">
                                Réserver ma séance
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    @elseif ($order->includesCoaching() && $order->coaching_appointment_id !== null)
                        <p class="text-ink-soft mt-8 text-sm">
                            Votre séance de coaching est réservée.
                            <a href="{{ route('booking.confirmation', $order->coachingAppointment->token) }}"
                               class="underline underline-offset-2 hover:text-teal-700">Voir le rendez-vous</a>
                        </p>
                    @endif
                @endif

                <p class="text-ink-muted mt-8 text-xs">
                    Référence {{ $order->reference }} ·
                    {{ Number::currency($order->amount_cents / 100, in: 'EUR', locale: 'fr') }}
                </p>
            </div>

            <p class="mt-6 text-center text-sm">
                <a href="{{ route('home') }}" class="text-ink-muted hover:text-ink underline-offset-2 hover:underline">
                    Retour à l'accueil →
                </a>
            </p>
        </div>
    </main>
@endsection
