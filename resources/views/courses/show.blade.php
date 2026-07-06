@extends('layouts.site')

@section('title', $course->seo_title ?: $course->title.' · Formation')
@section('description', $course->seo_description ?: $course->subtitle)
@section('canonical', route('courses.show', $course))

@push('head')
    @php
        $cover = $course->getFirstMediaUrl('cover');
        $courseLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course->title,
            'description' => $course->seo_description ?: $course->subtitle ?: str(strip_tags($course->description ?? ''))->limit(300)->toString(),
            'url' => route('courses.show', $course),
            'image' => $cover ?: null,
            'inLanguage' => 'fr-FR',
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Vivre Pleinement',
                'url' => url('/'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => number_format($course->price, 2, '.', ''),
                'priceCurrency' => $course->currency,
                'availability' => 'https://schema.org/InStock',
                'url' => route('courses.show', $course),
                'category' => 'Paid',
            ],
            'hasCourseInstance' => [
                '@type' => 'CourseInstance',
                'courseMode' => 'Online',
                'courseWorkload' => $course->duration_minutes ? 'PT'.$course->duration_minutes.'M' : null,
            ],
        ], fn ($value) => $value !== null && $value !== '');

        $courseLd['hasCourseInstance'] = array_filter($courseLd['hasCourseInstance']);
    @endphp
    <script type="application/ld+json">{!! json_encode($courseLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@php
    use Illuminate\Support\Number;

    $lessonCount = $course->modules->sum(fn ($module) => $module->lessons->count());
@endphp

@section('body')
    <a href="#main" class="focus:bg-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60] focus:rounded-full focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
        Aller au contenu
    </a>

    @include('layouts.partials.navbar')

    <header class="to-cream-50 relative overflow-hidden bg-linear-to-b from-teal-100 via-teal-50/70 pt-32 pb-12 sm:pt-36 sm:pb-16">
        <div class="site-container">
            <x-breadcrumb :items="[
                ['label' => 'Accueil', 'url' => route('home')],
                ['label' => 'Formations', 'url' => route('courses.index')],
                ['label' => $course->title],
            ]" />

            <div class="mt-6 max-w-3xl">
                <h1 class="text-ink font-serif text-4xl font-medium tracking-tight sm:text-5xl">{{ $course->title }}</h1>
                @if ($course->subtitle)
                    <p class="text-ink-soft mt-5 text-lg">{{ $course->subtitle }}</p>
                @endif
            </div>
        </div>
    </header>

    <main id="main" class="bg-cream-50 py-12 sm:py-16 lg:py-20">
        <div class="site-container">
            @if (session('status'))
                <p class="mb-8 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">{{ session('status') }}</p>
            @endif

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-16">
                {{-- Contenu principal --}}
                <div class="lg:col-span-7">
                    @if ($course->intro_video_provider === 'youtube' && $course->intro_video_id)
                        <x-youtube-embed :youtubeId="$course->intro_video_id" :title="$course->title" />
                    @endif

                    @if ($course->description)
                        <div class="prose prose-teal mt-10 max-w-none">
                            {!! $course->description !!}
                        </div>
                    @endif

                    @if (! empty($course->outcomes))
                        <div class="ring-ink/5 mt-10 rounded-4xl bg-white p-6 shadow-sm ring-1 sm:p-8">
                            <h2 class="text-ink font-serif text-2xl font-medium">Ce que vous allez apprendre</h2>
                            <ul class="mt-5 space-y-3">
                                @foreach ($course->outcomes as $outcome)
                                    <li class="text-ink-soft flex items-start gap-3 text-sm">
                                        <svg class="mt-0.5 size-5 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span>{{ is_array($outcome) ? ($outcome['value'] ?? '') : $outcome }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Programme --}}
                    <div class="mt-10">
                        <h2 class="text-ink font-serif text-2xl font-medium">Programme</h2>
                        <p class="text-ink-muted mt-1 text-sm">{{ $course->modules->count() }} module(s) · {{ $lessonCount }} leçon(s)</p>

                        <div class="mt-6 space-y-4">
                            @foreach ($course->modules as $module)
                                <div class="ring-ink/5 overflow-hidden rounded-3xl bg-white ring-1">
                                    <div class="border-ink/5 border-b px-6 py-4">
                                        <h3 class="text-ink font-medium">{{ $module->title }}</h3>
                                    </div>
                                    <ul class="divide-ink/5 divide-y">
                                        @foreach ($module->lessons as $lesson)
                                            <li class="flex items-center gap-3 px-6 py-3.5 text-sm">
                                                @if ($lesson->is_free_preview)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">Aperçu</span>
                                                @else
                                                    <svg class="text-ink-muted size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                @endif
                                                <span class="text-ink-soft flex-1">{{ $lesson->title }}</span>
                                                @if ($lesson->is_free_preview)
                                                    <a href="{{ route('student.lesson', [$course, $lesson]) }}" class="font-medium text-teal-700 hover:text-teal-800">Voir →</a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Carte d'achat (sticky) --}}
                <aside class="lg:col-span-5">
                    <div class="lg:sticky lg:top-28">
                        <div class="ring-ink/5 overflow-hidden rounded-4xl bg-white shadow-sm ring-1">
                            @if ($cover = $course->getFirstMedia('cover'))
                                <x-responsive-image :media="$cover" :alt="$course->title" sizes="(min-width: 1024px) 480px, 100vw" class="aspect-[16/10] w-full object-cover" />
                            @endif

                            <div class="p-6 sm:p-8">
                                <p class="text-ink font-serif text-3xl font-medium">{{ Number::currency($course->price, in: 'EUR', locale: 'fr') }}</p>
                                <p class="text-ink-muted mt-1 text-sm">Paiement unique · accès à vie</p>

                                @if ($hasAccess)
                                    <x-button :href="route('student.course', $course)" class="mt-6 w-full" arrow>Accéder à la formation</x-button>
                                @elseif (auth('student')->check())
                                    <form method="POST" action="{{ route('courses.checkout.start', $course) }}" class="mt-6">
                                        @csrf
                                        <x-button type="submit" class="w-full" arrow>Acheter la formation</x-button>
                                    </form>
                                @else
                                    <x-button :href="route('student.register', ['course' => $course->slug])" class="mt-6 w-full" arrow>Acheter la formation</x-button>
                                    <p class="text-ink-muted mt-3 text-center text-xs">
                                        Déjà un compte ?
                                        <a href="{{ route('student.login') }}" class="font-medium text-teal-700 hover:text-teal-800">Se connecter</a>
                                    </p>
                                @endif

                                <ul class="mt-6 space-y-2.5">
                                    <li class="text-ink-soft flex items-center gap-2.5 text-sm">
                                        <svg class="size-4 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        Accès illimité, à votre rythme
                                    </li>
                                    <li class="text-ink-soft flex items-center gap-2.5 text-sm">
                                        <svg class="size-4 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        Paiement sécurisé via Stripe
                                    </li>
                                    <li class="text-ink-soft flex items-center gap-2.5 text-sm">
                                        <svg class="size-4 shrink-0 text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        Suivi de votre progression
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    @include('home.sections.footer')
@endsection
