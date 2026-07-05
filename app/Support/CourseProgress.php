<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Student;

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
}
