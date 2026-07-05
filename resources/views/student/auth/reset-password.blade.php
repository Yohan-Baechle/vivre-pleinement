@extends('layouts.student')

@section('title', 'Réinitialiser le mot de passe · Espace formation')

@php
    $fieldClasses = 'mt-2 w-full rounded-2xl border-0 bg-cream-50 px-4 py-3 text-sm text-ink ring-1 ring-ink/10 transition placeholder:text-ink-muted focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden';
@endphp

@section('student')
    <div class="site-container">
        <div class="mx-auto max-w-md">
            <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight">Nouveau mot de passe</h1>

                <form method="POST" action="{{ route('student.password.update') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email"
                               class="{{ $fieldClasses }} @error('email') ring-rose-400 @enderror">
                        @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" required autofocus autocomplete="new-password"
                               class="{{ $fieldClasses }} @error('password') ring-rose-400 @enderror">
                        @error('password')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                               class="{{ $fieldClasses }}">
                    </div>

                    <x-button type="submit" class="w-full" arrow>Réinitialiser</x-button>
                </form>
            </div>
        </div>
    </div>
@endsection
