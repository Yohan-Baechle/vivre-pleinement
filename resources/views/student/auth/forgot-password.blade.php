@extends('layouts.student')

@section('title', 'Mot de passe oublié · Espace formation')

@section('student')
    <div class="site-container flex flex-1 flex-col justify-center">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Mot de passe oublié</h1>
                <p class="text-ink-soft mt-2 text-sm">Indiquez votre email, nous vous enverrons un lien pour le réinitialiser.</p>

                @if (session('status'))
                    <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('student.password.email') }}" class="mt-6 space-y-5">
                    @csrf
                    <x-form-field name="email" label="Email" type="email" :value="old('email')" required autofocus autocomplete="email" />

                    <x-button type="submit" class="w-full" arrow>Envoyer le lien</x-button>
                </form>
            </div>

            <p class="text-ink-soft mt-6 text-center text-sm">
                <a href="{{ route('student.login') }}" class="font-medium text-teal-700 hover:text-teal-800">← Retour à la connexion</a>
            </p>
        </div>
    </div>
@endsection
