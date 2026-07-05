@extends('layouts.student')

@section('title', 'Mon compte · Espace formation')

@php
    $fieldClasses = 'mt-2 w-full rounded-2xl border-0 bg-cream-50 px-4 py-3 text-sm text-ink ring-1 ring-ink/10 transition placeholder:text-ink-muted focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden';
    $labelClasses = 'text-ink-muted block text-xs font-medium tracking-wider uppercase';
@endphp

@section('student')
    <div class="site-container">
        <x-student-nav :student="$student" />

        <div class="mx-auto max-w-2xl">
            <div>
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">Mon compte</h1>
                <p class="text-ink-soft mt-2 text-sm">Gérez vos informations personnelles et votre mot de passe.</p>
            </div>

            @if (session('status') === 'profile-updated')
                <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">Vos informations ont été mises à jour.</p>
            @elseif (session('status') === 'profile-updated-email-verification')
                <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">Vos informations ont été mises à jour. Un lien de confirmation a été envoyé à votre nouvelle adresse e-mail.</p>
            @elseif (session('status') === 'password-updated')
                <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">Votre mot de passe a été modifié.</p>
            @endif

            {{-- Profil --}}
            <section class="ring-ink/5 mt-8 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-8">
                <h2 class="text-ink font-serif text-xl font-medium">Informations personnelles</h2>
                <p class="text-ink-soft mt-1 text-sm">Mettre à jour votre nom et votre adresse e-mail.</p>

                <form method="POST" action="{{ route('student.account.profile') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="{{ $labelClasses }}">Prénom et nom</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $student->name) }}" required autocomplete="name"
                               class="{{ $fieldClasses }} @error('name') ring-rose-400 @enderror">
                        @error('name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $labelClasses }}">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $student->email) }}" required autocomplete="email"
                               class="{{ $fieldClasses }} @error('email') ring-rose-400 @enderror">
                        @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror

                        @if (! $student->hasVerifiedEmail())
                            <p class="mt-2 text-xs text-rose-700">
                                Votre adresse e-mail n'est pas encore confirmée.
                                <a href="{{ route('student.verification.notice') }}" class="underline hover:text-rose-800">Renvoyer le lien de confirmation</a>
                            </p>
                        @endif
                    </div>

                    <x-button type="submit">Enregistrer</x-button>
                </form>
            </section>

            {{-- Mot de passe --}}
            <section class="ring-ink/5 mt-6 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-8">
                <h2 class="text-ink font-serif text-xl font-medium">Mot de passe</h2>
                <p class="text-ink-soft mt-1 text-sm">Choisissez un mot de passe long et unique pour sécuriser votre compte.</p>

                <form method="POST" action="{{ route('student.account.password') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="{{ $labelClasses }}">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                               class="{{ $fieldClasses }} @error('current_password') ring-rose-400 @enderror">
                        @error('current_password')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="{{ $labelClasses }}">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" autocomplete="new-password"
                               class="{{ $fieldClasses }} @error('password') ring-rose-400 @enderror">
                        @error('password')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="{{ $labelClasses }}">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                               class="{{ $fieldClasses }}">
                    </div>

                    <x-button type="submit">Modifier le mot de passe</x-button>
                </form>
            </section>

            {{-- Zone danger : suppression RGPD --}}
            <section class="ring-rose-soft bg-rose-soft/20 mt-6 rounded-4xl p-6 ring-1 sm:p-8">
                <h2 class="text-ink font-serif text-xl font-medium">Supprimer mon compte</h2>
                <p class="text-ink-soft mt-1 text-sm">Vos données personnelles seront effacées. Vos justificatifs d'achat sont conservés de façon anonymisée pour nos obligations comptables. Cette action est irréversible.</p>

                <button type="button" data-open-delete-dialog
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-medium text-rose-700 ring-1 ring-rose-300 transition hover:bg-rose-50">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Supprimer mon compte
                </button>
            </section>
        </div>
    </div>

    {{-- Modale de confirmation de suppression --}}
    <dialog id="delete-account-dialog"
            class="bg-transparent backdrop:bg-ink/50 backdrop:backdrop-blur-sm m-auto w-full max-w-md p-4 open:flex">
        <div class="ring-ink/5 w-full rounded-3xl bg-white p-6 shadow-xl ring-1 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                </span>
                <div>
                    <h2 class="text-ink font-serif text-xl font-medium">Supprimer définitivement votre compte&nbsp;?</h2>
                    <p class="text-ink-soft mt-2 text-sm">Cette action est <strong>irréversible</strong>. Vos données personnelles et votre progression seront effacées. Vous perdrez l'accès à vos formations.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('student.account.destroy') }}" class="mt-6">
                @csrf
                @method('DELETE')

                <label for="delete-confirm" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">
                    Pour confirmer, tapez <span class="text-rose-700">SUPPRIMER</span>
                </label>
                <input type="text" id="delete-confirm" data-delete-confirm autocomplete="off" autocapitalize="characters"
                       class="mt-2 w-full rounded-2xl border-0 bg-cream-50 px-4 py-3 text-sm text-ink ring-1 ring-ink/10 transition placeholder:text-ink-muted focus:bg-white focus:ring-2 focus:ring-rose-400 focus:outline-hidden"
                       placeholder="SUPPRIMER">

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" data-close-delete-dialog
                            class="text-ink-soft inline-flex items-center justify-center rounded-full bg-white px-5 py-2.5 text-sm font-medium ring-1 ring-ink/10 transition hover:bg-cream-50">
                        Annuler
                    </button>
                    <button type="submit" data-delete-submit disabled
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-rose-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40">
                        Supprimer mon compte
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        (() => {
            const dialog = document.getElementById('delete-account-dialog');
            if (! dialog) return;

            const openBtn = document.querySelector('[data-open-delete-dialog]');
            const closeBtn = dialog.querySelector('[data-close-delete-dialog]');
            const input = dialog.querySelector('[data-delete-confirm]');
            const submit = dialog.querySelector('[data-delete-submit]');

            const reset = () => {
                input.value = '';
                submit.disabled = true;
            };

            openBtn?.addEventListener('click', () => {
                reset();
                dialog.showModal();
                input.focus();
            });

            closeBtn?.addEventListener('click', () => dialog.close());

            input?.addEventListener('input', () => {
                submit.disabled = input.value.trim().toUpperCase() !== 'SUPPRIMER';
            });

            // Clic sur le fond (backdrop) : ferme la modale.
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });

            dialog.addEventListener('close', reset);
        })();
    </script>
@endsection
