@extends('layouts.student')

@section('title', 'Créer votre compte formation · Laura Baechlé')

@section('student')
    <div class="site-container flex flex-1 flex-col justify-center">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Créer un compte</h1>
                <p class="text-ink-soft mt-2 text-sm">Quelques secondes suffisent pour accéder à vos formations.</p>

                <form method="POST" action="{{ route('student.register.store') }}" class="mt-6 space-y-5">
                    @csrf
                    @if ($intendedCourse)
                        <input type="hidden" name="course" value="{{ $intendedCourse }}">
                    @endif

                    <x-form-field name="name" label="Prénom et nom" :value="old('name')" required autofocus autocomplete="name" />

                    <x-form-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" />

                    <x-form-field name="password" label="Mot de passe" type="password" required autocomplete="new-password" />

                    <x-form-field name="password_confirmation" label="Confirmer le mot de passe" type="password" required autocomplete="new-password" :show-error-ring="false" :show-error-message="false" />

                    <x-button type="submit" class="w-full" arrow>Créer mon compte</x-button>

                    <p class="text-ink-muted text-xs">
                        En créant un compte, vous acceptez nos
                        <a href="{{ route('legal.cgv') }}" class="underline hover:text-teal-700">conditions générales de vente</a>
                        et notre
                        <a href="{{ route('legal.privacy') }}" class="underline hover:text-teal-700">politique de confidentialité</a>.
                    </p>
                </form>
            </div>

            <p class="text-ink-soft mt-6 text-center text-sm">
                Vous avez déjà un compte ?
                <a href="{{ route('student.login') }}" class="font-medium text-teal-700 hover:text-teal-800">Se connecter</a>
            </p>
        </div>
    </div>
@endsection
