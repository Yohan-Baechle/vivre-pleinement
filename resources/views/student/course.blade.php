@extends('layouts.student')

@section('title', $course->title.' · Mon espace')

@section('student')
    <div class="site-container">
        <x-breadcrumb :items="[
            ['label' => 'Mon espace', 'url' => route('student.dashboard')],
            ['label' => $course->title],
        ]" />

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <h1 class="text-ink font-serif text-3xl font-medium tracking-tight sm:text-4xl">{{ $course->title }}</h1>
                @if ($course->subtitle)
                    <p class="text-ink-soft mt-3 text-lg">{{ $course->subtitle }}</p>
                @endif

                <div class="mt-6 max-w-md">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-muted">Votre progression</span>
                        <span class="text-ink font-medium">{{ $progress }}%</span>
                    </div>
                    <div class="bg-cream-200 mt-2 h-2.5 overflow-hidden rounded-full">
                        <div class="h-full rounded-full bg-teal-600 transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                @if ($firstLesson)
                    <x-button :href="route('student.lesson', [$course, $firstLesson])" class="mt-8" arrow>
                        {{ $progress > 0 ? 'Reprendre la formation' : 'Commencer la formation' }}
                    </x-button>
                @endif
            </div>
        </div>

        {{-- Programme --}}
        <div class="mt-12 space-y-5">
            @foreach ($course->modules as $module)
                <div class="ring-ink/5 overflow-hidden rounded-3xl bg-white ring-1">
                    <div class="border-ink/5 border-b px-6 py-4">
                        <h2 class="text-ink font-medium">{{ $module->title }}</h2>
                    </div>
                    <ul class="divide-ink/5 divide-y">
                        @foreach ($module->lessons as $lesson)
                            @php $isDone = in_array($lesson->id, $completedLessonIds, true); @endphp
                            <li>
                                <a href="{{ route('student.lesson', [$course, $lesson]) }}" class="hover:bg-cream-50 flex items-center gap-3 px-6 py-4 transition">
                                    @if ($isDone)
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-teal-600 text-white">
                                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        </span>
                                    @else
                                        <span class="border-ink/15 flex size-6 shrink-0 items-center justify-center rounded-full border"></span>
                                    @endif
                                    <span class="text-ink-soft flex-1 text-sm">{{ $lesson->title }}</span>
                                    @if ($lesson->duration_seconds)
                                        <span class="text-ink-muted text-xs">{{ gmdate('i:s', $lesson->duration_seconds) }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endsection
