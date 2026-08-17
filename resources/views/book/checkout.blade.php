@extends('layouts.site')

@php
    use Illuminate\Support\Number;

    $amountLabel = Number::currency($product->price, in: 'EUR', locale: 'fr');
    $includesCoaching = $product->slug === 'livre-coaching';
@endphp

@section('title', 'Commande · '.$product->name)

@push('head')
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Finalisez votre commande du livre en quelques secondes.">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="bg-cream-50 pt-32 pb-20 sm:pt-36">
        <div class="mx-auto max-w-xl px-4 sm:px-6">
            <h1 class="text-ink text-center font-serif text-3xl font-medium tracking-tight sm:text-4xl">
                Votre commande
            </h1>
            <p class="text-ink-soft mt-3 text-center text-sm">
                Vos coordonnées, puis le paiement. C'est à cette adresse que le livre vous sera envoyé.
            </p>

            <div class="ring-ink/5 mt-8 rounded-3xl bg-white p-6 shadow-xs ring-1">
                <p class="text-xs font-medium tracking-wider text-teal-700 uppercase">Formule choisie</p>
                <div class="mt-3 flex items-baseline justify-between gap-4">
                    <p class="text-ink font-serif text-xl font-medium">{{ $product->name }}</p>
                    <p class="text-ink font-serif text-xl font-medium">{{ $amountLabel }}</p>
                </div>
                <p class="text-ink-soft mt-1 text-sm">
                    Paiement unique · TTC
                    @if ($includesCoaching)
                        · 1 h de coaching incluse
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('book.start', $product->slug) }}"
                  class="ring-ink/5 mt-6 space-y-5 rounded-3xl bg-white p-6 shadow-xs ring-1 sm:p-8">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="text-ink block text-sm font-medium">Prénom</label>
                        <input type="text" name="first_name" id="first_name" required autocomplete="given-name"
                               value="{{ old('first_name') }}"
                               class="border-ink/15 bg-cream-50 text-ink mt-2 block w-full rounded-2xl border px-4 py-3 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/30">
                        @error('first_name')
                            <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="text-ink block text-sm font-medium">Nom</label>
                        <input type="text" name="last_name" id="last_name" required autocomplete="family-name"
                               value="{{ old('last_name') }}"
                               class="border-ink/15 bg-cream-50 text-ink mt-2 block w-full rounded-2xl border px-4 py-3 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/30">
                        @error('last_name')
                            <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="text-ink block text-sm font-medium">Email</label>
                    <input type="email" name="email" id="email" required autocomplete="email"
                           value="{{ old('email') }}"
                           class="border-ink/15 bg-cream-50 text-ink mt-2 block w-full rounded-2xl border px-4 py-3 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-500/30">
                    <p class="text-ink-muted mt-1.5 text-xs">Le lien de téléchargement y sera envoyé.</p>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Piège à robots : un humain ne remplit jamais ce champ. --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">Ne remplissez pas ce champ</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="consent" value="1" required
                           class="border-ink/20 bg-cream-50 mt-1 size-4 rounded-sm text-teal-700 focus:ring-2 focus:ring-teal-500">
                    <span class="text-ink-soft text-xs">
                        J'accepte que mes données soient utilisées pour traiter ma commande, conformément à la
                        <a href="{{ route('legal.privacy') }}" class="underline underline-offset-2 hover:text-teal-700">politique de confidentialité</a>.
                    </span>
                </label>
                @error('consent')
                    <p class="text-xs text-rose-700">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800">
                    Continuer vers le paiement
                    <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm">
                <a href="{{ route('book.show') }}" class="text-ink-muted hover:text-ink underline-offset-2 hover:underline">
                    ← Retour au livre
                </a>
            </p>
        </div>
    </main>
@endsection
