<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Student;
use Illuminate\Support\Collection;

class CourseProgress
{
    /**
     * Nombre total de leçons d'une formation.
     */
    public static function total(Course $course): int
    {
        return $course->lessons()->count();
    }

    /**
     * Nombre de leçons terminées par l'élève pour cette formation.
     */
    public static function completed(Student $student, Course $course): int
    {
        return $student->lessonProgress()
            ->whereNotNull('completed_at')
            ->whereIn('lesson_id', $course->lessons()->select('lessons.id'))
            ->count();
    }

    /**
     * Pourcentage de progression (0-100), arrondi à l'entier.
     */
    public static function percent(Student $student, Course $course): int
    {
        $total = self::total($course);

        if ($total === 0) {
            return 0;
        }

        return (int) round(self::completed($student, $course) / $total * 100);
    }

    /**
     * Pourcentage de progression pour plusieurs formations en 2 requêtes
     * agrégées au total, au lieu de 2 requêtes par formation (tableau de bord
     * élève avec ses formations achetées).
     *
     * @param  Collection<int, Course>  $courses
     * @return Collection<int, int> pourcentage par id de formation
     */
    public static function percentForCourses(Student $student, Collection $courses): Collection
    {
        $courseIds = $courses->pluck('id');

        if ($courseIds->isEmpty()) {
            return collect();
        }

        $totals = Lesson::query()
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->whereIn('modules.course_id', $courseIds)
            ->selectRaw('modules.course_id as course_id, count(*) as total')
            ->groupBy('modules.course_id')
            ->pluck('total', 'course_id');

        $completed = LessonProgress::query()
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('modules', 'modules.id', '=', 'lessons.module_id')
            ->where('lesson_progress.student_id', $student->id)
            ->whereNotNull('lesson_progress.completed_at')
            ->whereIn('modules.course_id', $courseIds)
            ->selectRaw('modules.course_id as course_id, count(*) as completed')
            ->groupBy('modules.course_id')
            ->pluck('completed', 'course_id');

        return $courseIds->mapWithKeys(function ($courseId) use ($totals, $completed) {
            $total = (int) ($totals[$courseId] ?? 0);

            if ($total === 0) {
                return [$courseId => 0];
            }

            $percent = (int) round(((int) ($completed[$courseId] ?? 0)) / $total * 100);

            return [$courseId => $percent];
        });
    }
}
