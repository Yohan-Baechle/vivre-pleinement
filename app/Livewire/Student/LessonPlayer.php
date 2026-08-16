<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\Lesson;
use App\Support\CourseProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LessonPlayer extends Component
{
    public Course $course;

    public Lesson $lesson;

    public bool $completed = false;

    public int $progress = 0;

    public function mount(Course $course, Lesson $lesson): void
    {
        $this->course = $course;
        $this->lesson = $lesson;
        $this->refreshState();
    }

    /**
     * Marque la leçon courante comme terminée pour l'élève connecté. Idempotent
     * grâce à updateOrCreate sur la contrainte unique (student, lesson).
     */
    public function markComplete(): void
    {
        $student = auth('student')->user();

        abort_if($student === null, 403);
        abort_unless($student->hasAccessTo($this->course) || $this->lesson->is_free_preview, 403);

        $student->lessonProgress()->updateOrCreate(
            ['lesson_id' => $this->lesson->id],
            ['completed_at' => now()],
        );

        $this->refreshState();
    }

    public function markIncomplete(): void
    {
        $student = auth('student')->user();

        abort_if($student === null, 403);
        abort_unless($student->hasAccessTo($this->course) || $this->lesson->is_free_preview, 403);

        $student->lessonProgress()
            ->where('lesson_id', $this->lesson->id)
            ->delete();

        $this->refreshState();
    }

    /**
     * Leçons de la formation, à plat et dans l'ordre, pour la navigation.
     *
     * @return Collection<int, Lesson>
     */
    #[Computed]
    public function lessons(): Collection
    {
        return $this->course->modules()
            ->with(['lessons' => fn ($query) => $query->orderBy('position')])
            ->get()
            ->flatMap->lessons
            ->values();
    }

    public function nextLesson(): ?Lesson
    {
        $lessons = $this->lessons;
        $index = $lessons->search(fn (Lesson $lesson) => $lesson->id === $this->lesson->id);

        return $index === false ? null : $lessons->get($index + 1);
    }

    public function previousLesson(): ?Lesson
    {
        $lessons = $this->lessons;
        $index = $lessons->search(fn (Lesson $lesson) => $lesson->id === $this->lesson->id);

        return $index === false || $index === 0 ? null : $lessons->get($index - 1);
    }

    private function refreshState(): void
    {
        $student = auth('student')->user();

        $this->completed = $student !== null && $student->lessonProgress()
            ->where('lesson_id', $this->lesson->id)
            ->whereNotNull('completed_at')
            ->exists();

        $this->progress = $student !== null
            ? CourseProgress::percent($student, $this->course)
            : 0;
    }

    public function render(): View
    {
        return view('livewire.student.lesson-player', [
            'next' => $this->nextLesson(),
            'previous' => $this->previousLesson(),
        ]);
    }
}
