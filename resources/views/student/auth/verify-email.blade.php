@extends('layouts.student')

@section('title', 'Vérifiez votre adresse e-mail · Espace formation')

@section('student')
    <div class="site-container flex flex-1 flex-col justify-center">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Confirmez votre e-mail</h1>
                <p class="text-ink-soft mt-2 text-sm">
                    Merci pour votre inscription&nbsp;! Avant de continuer, veuillez confirmer votre adresse e-mail
                    en cliquant sur le lien que nous venons de vous envoyer. Pensez à vérifier vos courriers indésirables.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">
                        Un nouveau lien de confirmation vient de vous être envoyé.
                    </p>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <form method="POST" action="{{ route('student.verification.send') }}">
                        @csrf
                        <x-button type="submit" arrow>Renvoyer le lien</x-button>
                    </form>

                    <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit" class="text-ink-muted text-sm font-medium transition hover:text-teal-700">Se déconnecter</button>
                    </form>
                </div>
            </div>

            <p class="text-ink-soft mt-6 text-center text-sm">
                Vous vous êtes trompé d'adresse&nbsp;?
                <a href="{{ route('student.account.edit') }}" class="font-medium text-teal-700 hover:text-teal-800">Modifier mon e-mail</a>
            </p>
        </div>
    </div>
@endsection
