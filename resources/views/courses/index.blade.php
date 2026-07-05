@extends('layouts.site')

@section('title', 'Formations en ligne · Laura Baechlé')
@section('description', "Des formations en ligne pour apprendre, à votre rythme, à apaiser l'anxiété et retrouver une vie sereine. Accès à vie, vidéos et ressources pratiques.")
@section('canonical', route('courses.index'))

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Formations'],
            ]" />

            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    <span class="size-1.5 rounded-full bg-teal-500"></span>
                    Apprendre à votre rythme
                </p>
                <h1 class="text-ink mt-5 font-serif text-4xl font-medium tracking-tight sm:text-5xl lg:text-6xl">
                    Les formations
                </h1>
                <p class="text-ink-soft mt-5 max-w-2xl text-base sm:text-lg">
                    Des parcours pas à pas, en vidéo, pour avancer chez vous, à votre propre rythme. Accès à vie, où que vous soyez.
                </p>
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            @if ($courses->isEmpty())
                <div class="ring-ink/5 mx-auto max-w-xl rounded-4xl bg-white p-10 text-center shadow-sm ring-1">
                    <h2 class="text-ink font-serif text-2xl font-medium">Bientôt disponible</h2>
                    <p class="text-ink-soft mt-3 text-sm">Les premières formations arrivent prochainement. En attendant, vous pouvez explorer le blog et les vidéos.</p>
                    <div class="mt-6 flex justify-center gap-3">
                        <x-button :href="route('videos.index')" variant="secondary" size="md">Voir les vidéos</x-button>
                        <x-button :href="route('blog.index')" variant="secondary" size="md">Lire le blog</x-button>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                    @foreach ($courses as $course)
                        <x-course-card :course="$course" class="relative" />
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    @include('home.sections.footer')
@endsection
