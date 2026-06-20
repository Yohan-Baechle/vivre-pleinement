@extends('layouts.site')

@php
    $email = \App\Support\SiteContact::email();
    $phone = \App\Support\SiteContact::phone();
    $phoneHref = \App\Support\SiteContact::phoneHref();
    $socials = \App\Support\SiteContact::socials();
    $socialIcons = [
        'Instagram' => 'M7.5 2C4.46 2 2 4.46 2 7.5v9C2 19.54 4.46 22 7.5 22h9c3.04 0 5.5-2.46 5.5-5.5v-9C22 4.46 19.54 2 16.5 2h-9zM20 16.5c0 1.93-1.57 3.5-3.5 3.5h-9C5.57 20 4 18.43 4 16.5v-9C4 5.57 5.57 4 7.5 4h9C18.43 4 20 5.57 20 7.5v9zM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm5.5-9.25a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5z',
        'Facebook' => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.51 1.5-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.89h-2.33v6.99A10 10 0 0 0 22 12z',
        'YouTube' => 'M23.5 6.2a3 3 0 0 0-2.1-2.12C19.55 3.5 12 3.5 12 3.5s-7.55 0-9.4.58A3 3 0 0 0 .5 6.2C0 8.05 0 12 0 12s0 3.95.5 5.8a3 3 0 0 0 2.1 2.12c1.85.58 9.4.58 9.4.58s7.55 0 9.4-.58a3 3 0 0 0 2.1-2.12C24 15.95 24 12 24 12s0-3.95-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z',
        'TikTok' => 'M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-5.86 11.95 6.85 6.85 0 0 0 11.13-5.37V8.6a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.81-.03z',
    ];

    $contactFaq = [
        [
            'q' => "Que se passe-t-il après l'envoi de mon message ?",
            'a' => "Votre message m'arrive directement. Je le lis personnellement et je vous réponds sous 48h ouvrées, à l'adresse email que vous avez indiquée. Pensez à vérifier vos courriers indésirables au cas où.",
        ],
        [
            'q' => "Quel est le délai de réponse ?",
            'a' => "Je réponds à tous les messages sous 48h ouvrées (du lundi au vendredi). Pour une demande urgente, n'hésitez pas à l'indiquer dans votre message.",
        ],
        [
            'q' => "Comment prendre rendez-vous plutôt que d'écrire ?",
            'a' => "Si vous souhaitez directement réserver votre rendez-vous découverte gratuit, vous pouvez le faire en quelques clics depuis la page de réservation. Le formulaire de contact, lui, est parfait pour une question avant de vous lancer.",
        ],
        [
            'q' => "Vous avez déjà la réponse à ma question ?",
            'a' => "Pensez à consulter la FAQ générale et les articles du blog avant. Beaucoup de questions sur les troubles anxieux, l'accompagnement ou les tarifs y trouvent déjà une réponse.",
        ],
    ];
@endphp

@section('title', 'Contact · Laura Baechlé – Vivre Pleinement')
@section('description', "Une question, une demande d'accompagnement ? Contactez Laura Baechlé via le formulaire, par email, par téléphone ou sur les réseaux sociaux.")
@section('canonical', route('contact'))

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Contact'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Me contacter
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Parlons de vous.
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Une question, un doute, l'envie d'avancer ? Écrivez-moi. Je vous réponds personnellement sous 48h ouvrées.
                </p>
            </div>
        </div>
    </header>

    @php
        $fieldClasses = 'mt-2 w-full rounded-2xl border-0 bg-cream-50 px-4 py-3 text-sm text-ink ring-1 ring-ink/10 transition placeholder:text-ink-muted focus:bg-white focus:ring-2 focus:ring-teal-500 focus:outline-hidden';
        $fieldError = 'ring-rose-400 bg-rose-soft/20';
    @endphp

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-16">
                {{-- Formulaire --}}
                <div class="lg:col-span-7">
                    <div class="ring-ink/5 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-10">
                    @if (session('status'))
                        <p class="mb-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">
                            {{ session('status') }}
                        </p>
                    @endif

                    @if ($errors->has('message') && ! $errors->has('first_name'))
                        <p class="bg-rose-soft/40 text-ink ring-rose-soft mb-6 rounded-2xl px-4 py-3 text-sm ring-1">
                            {{ $errors->first('message') }}
                        </p>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}" class="space-y-5" novalidate>
                        @csrf
                        <input type="hidden" name="ts" value="{{ time() }}">

                        {{-- Honeypot --}}
                        <div aria-hidden="true" class="absolute top-auto -left-[9999px] size-px overflow-hidden">
                            <label for="website">Site web (ne pas remplir)</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Prénom *</label>
                                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"
                                       class="{{ $fieldClasses }} @error('first_name') {{ $fieldError }} @enderror">
                                @error('first_name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="last_name" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Nom</label>
                                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name"
                                       class="{{ $fieldClasses }} @error('last_name') {{ $fieldError }} @enderror">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="email" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Email *</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                                       class="{{ $fieldClasses }} @error('email') {{ $fieldError }} @enderror">
                                @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="phone" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Téléphone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="Optionnel"
                                       class="{{ $fieldClasses }}">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Objet *</label>
                            <select id="subject" name="subject" required
                                    class="{{ $fieldClasses }} @error('subject') {{ $fieldError }} @enderror">
                                <option value="">– Choisir l'objet –</option>
                                <option value="rdv" @selected(old('subject', request('subject')) ==='rdv')>Prendre rendez-vous</option>
                                <option value="question" @selected(old('subject', request('subject')) ==='question')>Question sur l'accompagnement</option>
                                <option value="partenariat" @selected(old('subject', request('subject')) ==='partenariat')>Partenariat</option>
                                <option value="media" @selected(old('subject', request('subject')) ==='media')>Demande presse / média</option>
                                <option value="autre" @selected(old('subject', request('subject')) ==='autre')>Autre</option>
                            </select>
                            @error('subject')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="message" class="text-ink-muted block text-xs font-medium tracking-wider uppercase">Message *</label>
                            <textarea id="message" name="message" rows="6" required minlength="20" maxlength="5000"
                                      class="{{ $fieldClasses }} @error('message') {{ $fieldError }} @enderror"
                                      placeholder="Dites-moi ce qui vous amène...">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                                   class="border-ink/20 bg-cream-50 mt-1 size-4 rounded-sm text-teal-700 focus:ring-2 focus:ring-teal-500">
                            <span class="text-ink-soft text-xs">
                                J'accepte que mes informations soient utilisées uniquement pour répondre à ma demande. Aucune diffusion à des tiers.
                            </span>
                        </label>
                        @error('consent')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror

                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <button type="submit" class="group inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800">
                                Envoyer le message
                                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                            </button>
                            <p class="text-ink-muted text-xs">* Champs requis</p>
                        </div>
                    </form>
                </div>
                </div>

                {{-- Coordonnées + réseaux --}}
                <aside class="space-y-6 lg:col-span-5">
                    <div class="text-cream-100 rounded-4xl bg-linear-to-br from-teal-700 to-teal-800 p-6 sm:p-8">
                        <h2 class="font-serif text-2xl font-medium text-white">Autres moyens</h2>
                        <p class="text-cream-100/80 mt-2 text-sm">Préférez le téléphone ou l'email ? C'est aussi possible.</p>

                        <ul class="mt-6 space-y-4">
                            <li>
                                <a href="mailto:{{ $email }}" class="group flex items-start gap-3 text-sm transition hover:text-white">
                                    <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/15 transition group-hover:bg-white/20">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <rect x="2" y="4" width="20" height="16" rx="3"/><path d="m2 7 10 6 10-6"/>
                                        </svg>
                                    </span>
                                    <span>
                                        <span class="text-cream-100/60 block text-xs tracking-wider uppercase">Email</span>
                                        <span class="font-medium">{{ $email }}</span>
                                    </span>
                                </a>
                            </li>
                            @if ($phone)
                                <li>
                                    <a href="tel:{{ $phoneHref }}" class="group flex items-start gap-3 text-sm transition hover:text-white">
                                        <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/15 transition group-hover:bg-white/20">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92Z"/>
                                            </svg>
                                        </span>
                                        <span>
                                            <span class="text-cream-100/60 block text-xs tracking-wider uppercase">Téléphone</span>
                                            <span class="font-medium">{{ $phone }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endif
                        </ul>

                        @if (! empty($socials))
                            <div class="mt-8 border-t border-white/10 pt-6">
                                <p class="text-cream-100/60 text-xs tracking-wider uppercase">Sur les réseaux</p>
                                <ul class="mt-3 flex items-center gap-2">
                                    @foreach ($socials as $name => $href)
                                        <li>
                                            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $name }}" class="flex size-9 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/15 transition hover:bg-white/20">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $socialIcons[$name] }}"/></svg>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Réassurance --}}
                    <ul class="space-y-3 px-1">
                        <li class="text-ink-soft flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 size-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            <span>Une réponse personnelle sous <span class="text-ink font-medium">48h ouvrées</span>.</span>
                        </li>
                        <li class="text-ink-soft flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 size-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>Vos informations restent <span class="text-ink font-medium">strictement confidentielles</span>.</span>
                        </li>
                        <li class="text-ink-soft flex items-start gap-3 text-sm">
                            <svg class="mt-0.5 size-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Z"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/>
                            </svg>
                            <span>Échange bienveillant, <span class="text-ink font-medium">sans jugement</span>.</span>
                        </li>
                    </ul>
                </aside>
            </div>
        </div>
    </main>

    <x-section
        eyebrow="Avant de m'écrire"
        title="Quelques questions fréquentes."
        bg="bg-white"
    >
        <div class="mx-auto max-w-3xl space-y-4">
            @foreach ($contactFaq as $item)
                <x-accordion-item :question="$item['q']" :open="$loop->first">
                    {{ $item['a'] }}
                </x-accordion-item>
            @endforeach
        </div>
    </x-section>

    @include('home.sections.footer')
@endsection
