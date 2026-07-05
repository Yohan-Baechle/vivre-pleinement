@extends('layouts.student')

@section('title', 'Créer votre compte formation · Laura Baechlé')

@php
    $fieldClasses = 'mt-2 w-full rounded-2xl border-0 bg-cream-50 px-4 py-3 text-sm text-ink ring-1 ring-ink/10 transition placeholder:text-ink-muted focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden';
@endphp

@section('student')
    <div class="site-container">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Créer un compte</h1>
                <p class="text-ink-soft mt-2 text-sm">Quelques secondes suffisent pour accéder à vos formations.</p>

                <form method="POST" action="{{ route('student.register.store') }}" class="mt-6 space-y-5">
                    @csrf
                    @if ($intendedCourse)
                        <input type="hidden" name="course" value="{{ $intendedCourse }}">
                    @endif

                    <div>
                        <label for="name" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Prénom et nom</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                               class="{{ $fieldClasses }} @error('name') ring-rose-400 @enderror">
                        @error('name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="{{ $fieldClasses }} @error('email') ring-rose-400 @enderror">
                        @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Mot de passe</label>
                        <input type="password" id="password" name="password" required autocomplete="new-password"
                               class="{{ $fieldClasses }} @error('password') ring-rose-400 @enderror">
                        @error('password')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                               class="{{ $fieldClasses }}">
                    </div>

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
