@extends('layouts.site')

@php
    $faq = \App\Support\Faq::all();
@endphp

@section('title', 'FAQ · Laura Baechlé - Vivre Pleinement')
@section('description', "Les réponses aux questions les plus fréquentes sur l'accompagnement des troubles anxieux : déroulement, tarifs, séances à distance, confidentialité et thérapie ACT.")
@section('canonical', route('faq'))
@section('og_title', 'Questions fréquentes · Laura Baechlé')
@section('og_description', "Tout ce que vous voulez savoir sur l'accompagnement ACT des troubles anxieux : séances, tarifs, confidentialité.")

@push('head')
    @php
        $faqLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            '@id' => route('faq').'#faq',
            'url' => route('faq'),
            'inLanguage' => 'fr-FR',
            'mainEntity' => collect($faq)->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ])->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'FAQ'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Questions fréquentes
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Tout ce que vous voulez savoir.
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Les questions qu'on me pose le plus souvent sur l'accompagnement, les séances et la thérapie ACT. Si la vôtre n'y figure pas, écrivez-moi.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            <div class="mx-auto max-w-3xl space-y-4">
                @foreach ($faq as $item)
                    <x-accordion-item :question="$item['q']" :open="$loop->first">
                        {{ $item['a'] }}
                    </x-accordion-item>
                @endforeach
            </div>

            <div class="mx-auto mt-12 max-w-3xl rounded-3xl bg-white p-8 text-center ring-1 ring-ink/5 sm:p-10">
                <h2 class="text-ink font-serif text-2xl font-medium">Une autre question&nbsp;?</h2>
                <p class="text-ink-soft mt-3 text-sm leading-relaxed">
                    Écrivez-moi, je vous réponds personnellement sous 48h ouvrées.
                </p>
                <a href="{{ route('contact') }}" class="group mt-6 inline-flex items-center gap-2 rounded-full bg-teal-700 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 sm:text-base">
                    Me contacter
                    <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </main>

    @include('home.sections.footer')
@endsection
