@extends('layouts.site')

@section('title', 'Merci · '.$course->title)

@push('head')
    <meta name="robots" content="noindex,nofollow">
    @unless ($hasAccess)
        <meta http-equiv="refresh" content="5">
    @endunless
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <main class="bg-cream-50 pt-32 pb-20 sm:pt-36">
        <div class="mx-auto max-w-xl px-4 text-center sm:px-6">
            @if ($hasAccess)
                <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <h1 class="text-ink mt-6 font-serif text-3xl font-medium tracking-tight sm:text-4xl">Merci, votre accès est ouvert !</h1>
                <p class="text-ink-soft mt-3 text-sm">Vous pouvez commencer dès maintenant, à votre rythme.</p>
                <x-button :href="route('student.course', $course)" class="mt-8" arrow>Accéder à ma formation</x-button>
            @else
                <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                    <svg class="size-8 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4z"/></svg>
                </span>
                <h1 class="text-ink mt-6 font-serif text-3xl font-medium tracking-tight sm:text-4xl">Paiement en cours de validation…</h1>
                <p class="text-ink-soft mt-3 text-sm">Cette page se rafraîchit automatiquement. Vous recevrez aussi un email de confirmation dès que votre accès est ouvert.</p>
                <x-button :href="route('student.dashboard')" variant="secondary" size="md" class="mt-8">Aller à mon espace</x-button>
            @endif
        </div>
    </main>
@endsection
