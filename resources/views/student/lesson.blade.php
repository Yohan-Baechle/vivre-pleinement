@extends('layouts.student')

@section('title', $lesson->title.' · '.$course->title)

@section('student')
    <div class="site-container">
        <x-breadcrumb :items="[
            ['label' => 'Mon espace', 'url' => route('student.dashboard')],
            ['label' => $course->title, 'url' => route('student.course', $course)],
            ['label' => $lesson->title],
        ]" />

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-12">
            {{-- Lecteur de leçon --}}
            <div class="lg:col-span-8">
                <livewire:student.lesson-player :course="$course" :lesson="$lesson" :key="$lesson->id" />
            </div>

            {{-- Sommaire de la formation --}}
            <aside class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <div class="ring-ink/5 overflow-hidden rounded-3xl bg-white ring-1">
                        <div class="border-ink/5 border-b px-5 py-4">
                            <p class="text-ink font-medium">{{ $course->title }}</p>
                            <div class="bg-cream-200 mt-3 h-1.5 overflow-hidden rounded-full">
                                <div class="h-full rounded-full bg-teal-600" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-ink-muted mt-1.5 text-xs">{{ $progress }}% terminé</p>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto">
                            @foreach ($course->modules as $module)
                                <div class="px-5 py-3">
                                    <p class="text-ink-muted text-xs font-medium tracking-wider uppercase">{{ $module->title }}</p>
                                    <ul class="mt-2 space-y-1">
                                        @foreach ($module->lessons as $item)
                                            @php
                                                $isDone = in_array($item->id, $completedLessonIds, true);
                                                $isCurrent = $item->id === $lesson->id;
                                            @endphp
                                            <li>
                                                <a href="{{ route('student.lesson', [$course, $item]) }}" @class([
                                                    'flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm transition',
                                                    'bg-teal-50 text-teal-800 font-medium' => $isCurrent,
                                                    'text-ink-soft hover:bg-cream-50' => ! $isCurrent,
                                                ])>
                                                    @if ($isDone)
                                                        <svg class="size-4 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                                    @else
                                                        <span class="border-ink/20 size-4 shrink-0 rounded-full border"></span>
                                                    @endif
                                                    <span class="flex-1">{{ $item->title }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
