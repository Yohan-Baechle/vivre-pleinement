@extends('layouts.site')

@section('title', "Commande en préparation | Vivre Pleinement")

@push('head')
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Le paiement en ligne sera disponible très prochainement.">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <section class="via-cream-50 to-cream-100 relative flex min-h-screen items-center justify-center bg-linear-to-b from-teal-100 px-4 py-32 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-xl text-center">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                <span class="size-1.5 rounded-full bg-teal-500"></span>
                Bientôt disponible
            </p>

            <h1 class="text-ink mt-6 font-serif text-3xl leading-tight font-medium tracking-tight sm:text-4xl lg:text-5xl">
                Le paiement en ligne arrive très bientôt.
            </h1>

            <p class="text-ink-soft mt-6 text-base sm:text-lg">
                La boutique sécurisée est en cours d'installation. En attendant, vous pouvez réserver votre exemplaire en m'écrivant directement.
            </p>

            <div class="text-ink ring-cream-200 mt-4 inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium ring-1">
                Formule choisie :
                <strong class="text-teal-700">
                    @if($offer === 'livre')
                        Le livre seul · 37&nbsp;€
                    @else
                        Le livre + coaching · 70&nbsp;€
                    @endif
                </strong>
            </div>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('contact') }}?subject=livre" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                    Me contacter pour réserver
                    <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
                <a href="{{ route('book.show') }}" class="text-ink-soft inline-flex items-center gap-2 text-sm font-medium transition hover:text-teal-700">
                    Retour à la page du livre
                </a>
            </div>
        </div>
    </section>

    @include('home.sections.footer')
@endsection
