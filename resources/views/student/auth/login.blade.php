@extends('layouts.student')

@section('title', 'Connexion à votre espace formation · Laura Baechlé')

@section('student')
    <div class="site-container flex flex-1 flex-col justify-center">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Connexion</h1>
                <p class="text-ink-soft mt-2 text-sm">Accédez à vos formations et reprenez là où vous vous êtes arrêté·e.</p>

                @if (session('status'))
                    <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('student.login.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <x-form-field name="email" label="Email" type="email" :value="old('email')" required autofocus autocomplete="email" />

                    <x-form-field name="password" label="Mot de passe" type="password" required autocomplete="current-password" />

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" value="1" class="border-ink/20 bg-cream-50 size-4 rounded-sm text-teal-700 focus:ring-2 focus:ring-teal-500">
                            <span class="text-ink-soft text-xs">Se souvenir de moi</span>
                        </label>
                        <a href="{{ route('student.password.request') }}" class="text-xs font-medium text-teal-700 hover:text-teal-800">Mot de passe oublié ?</a>
                    </div>

                    <x-button type="submit" class="w-full" arrow>Se connecter</x-button>
                </form>
            </div>

            <p class="text-ink-soft mt-6 text-center text-sm">
                Pas encore de compte ?
                <a href="{{ route('student.register') }}" class="font-medium text-teal-700 hover:text-teal-800">Créer un compte</a>
            </p>
        </div>
    </div>
@endsection
