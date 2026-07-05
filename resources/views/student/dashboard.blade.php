@extends('layouts.student')

@section('title', 'Mon espace formation · Laura Baechlé')

@section('student')
    <div class="site-container">
        <x-student-nav :student="$student" />

        <div>
            <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">Bonjour {{ $student->name }}</h1>
            <p class="text-ink-soft mt-2 text-sm">Retrouvez vos formations et reprenez votre progression.</p>
        </div>

        @if (request()->boolean('verified'))
            <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">Votre adresse e-mail a bien été confirmée. Bienvenue&nbsp;!</p>
        @elseif (session('status'))
            <p class="mt-6 rounded-2xl bg-teal-50 px-4 py-3 text-sm text-teal-800 ring-1 ring-teal-200">{{ session('status') }}</p>
        @endif

        <div class="mt-10">
            @if ($courses->isEmpty())
                <div class="ring-ink/5 rounded-4xl bg-white p-10 text-center shadow-sm ring-1">
                    <h2 class="text-ink font-serif text-2xl font-medium">Aucune formation pour l'instant</h2>
                    <p class="text-ink-soft mt-3 text-sm">Découvrez les formations disponibles pour commencer votre parcours.</p>
                    <div class="mt-6 flex justify-center">
                        <x-button :href="route('courses.index')" size="md" arrow>Voir les formations</x-button>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        <article class="group ring-ink/5 flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 transition hover:-translate-y-0.5 hover:shadow-md">
                            <a href="{{ route('student.course', $course) }}" class="block aspect-[16/10] overflow-hidden bg-linear-to-br from-teal-100 via-cream-100 to-rose-soft/40">
                                @if ($cover = $course->getFirstMedia('cover'))
                                    <x-responsive-image :media="$cover" :alt="$course->title" sizes="(min-width: 1024px) 400px, 100vw" class="size-full object-cover transition duration-500 group-hover:scale-105" />
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-6">
                                <h2 class="text-ink font-serif text-lg font-medium leading-snug transition group-hover:text-teal-700">
                                    <a href="{{ route('student.course', $course) }}">{{ $course->title }}</a>
                                </h2>

                                @php $pct = $progress[$course->id] ?? 0; @endphp
                                <div class="mt-4 flex-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-ink-muted">Progression</span>
                                        <span class="text-ink font-medium">{{ $pct }}%</span>
                                    </div>
                                    <div class="bg-cream-200 mt-2 h-2 overflow-hidden rounded-full">
                                        <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                <x-button :href="route('student.course', $course)" variant="secondary" size="sm" class="mt-5">
                                    {{ $pct > 0 ? 'Continuer' : 'Commencer' }}
                                </x-button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection
