<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array{0: Course, 1: Lesson}
 */
function courseWithLesson(bool $freePreview = false): array
{
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create([
        'module_id' => $module->id,
        'is_free_preview' => $freePreview,
    ]);

    return [$course, $lesson];
}

it('redirige un élève non inscrit vers la page de vente', function () {
    [$course, $lesson] = courseWithLesson();
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->get(route('student.lesson', [$course, $lesson]))
        ->assertRedirect(route('courses.show', $course));
});

it('donne accès à une leçon en aperçu gratuit sans achat', function () {
    [$course, $lesson] = courseWithLesson(freePreview: true);
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->get(route('student.lesson', [$course, $lesson]))
        ->assertOk();
});

it('donne accès aux leçons à un élève inscrit', function () {
    [$course, $lesson] = courseWithLesson();
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

    $this->actingAs($student, 'student')
        ->get(route('student.lesson', [$course, $lesson]))
        ->assertOk()
        ->assertSee($lesson->title);
});

it('redirige un élève dont l\'inscription a été remboursée vers la page de vente', function () {
    [$course, $lesson] = courseWithLesson();
    $student = Student::factory()->create();
    Enrollment::factory()->refunded()->create(['student_id' => $student->id, 'course_id' => $course->id]);

    $this->actingAs($student, 'student')
        ->get(route('student.lesson', [$course, $lesson]))
        ->assertRedirect(route('courses.show', $course));
});

it('redirige un élève dont l\'inscription est en attente de paiement vers la page de vente', function () {
    [$course, $lesson] = courseWithLesson();
    $student = Student::factory()->create();
    Enrollment::factory()->pending()->create(['student_id' => $student->id, 'course_id' => $course->id]);

    $this->actingAs($student, 'student')
        ->get(route('student.lesson', [$course, $lesson]))
        ->assertRedirect(route('courses.show', $course));
});

it('empêche un élève d\'accéder au contenu d\'une formation non achetée', function () {
    [$course, $lesson] = courseWithLesson();
    $otherCourse = Course::factory()->create();
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $otherCourse->id]);

    $this->actingAs($student, 'student')
        ->get(route('student.course', $course))
        ->assertRedirect(route('courses.show', $course));
});

it('protège l\'espace élève des visiteurs non connectés', function () {
    [$course, $lesson] = courseWithLesson();

    $this->get(route('student.course', $course))->assertRedirect(route('student.login'));
});
