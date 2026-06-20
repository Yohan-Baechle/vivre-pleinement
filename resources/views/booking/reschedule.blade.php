@extends('layouts.site')

@section('title', 'Reprogrammer mon rendez-vous · Vivre Pleinement')

@push('head')
    <meta name="robots" content="noindex,nofollow">
@endpush

@section('body')
    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Reprogrammation
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl">
                    Choisissez un nouveau créneau.
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    {{ $appointment->service->name }} - actuellement prévu le
                    {{ $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM à H\hi') }}.
                </p>
            </div>
        </div>
    </header>

    <main class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-10">
            @livewire('booking-calendar', ['service' => $appointment->service, 'rescheduleToken' => $appointment->token])
        </div>
    </main>

    @include('home.sections.footer')
@endsection
