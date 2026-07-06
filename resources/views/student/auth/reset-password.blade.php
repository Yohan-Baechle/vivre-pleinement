@extends('layouts.student')

@section('title', 'Réinitialiser le mot de passe · Espace formation')

@section('student')
    <div class="site-container flex flex-1 flex-col justify-center">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Nouveau mot de passe</h1>

                <form method="POST" action="{{ route('student.password.update') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-form-field name="email" label="Email" type="email" :value="old('email', $request->email)" required autocomplete="email" />

                    <x-form-field name="password" label="Nouveau mot de passe" type="password" required autofocus autocomplete="new-password" />

                    <x-form-field name="password_confirmation" label="Confirmer le mot de passe" type="password" required autocomplete="new-password" :show-error-ring="false" :show-error-message="false" />

                    <x-button type="submit" class="w-full" arrow>Réinitialiser</x-button>
                </form>
            </div>
        </div>
    </div>
@endsection
