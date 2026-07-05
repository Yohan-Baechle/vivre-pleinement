<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Support\StudentAnonymizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('anonymise le compte élève en conservant la vente', function () {
    $student = Student::factory()->create(['name' => 'Camille Dupont', 'email' => 'camille@example.com']);
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

    StudentAnonymizer::anonymize($student);

    $fresh = $student->fresh();
    expect($fresh->name)->toBe('Compte supprimé')
        ->and($fresh->email)->not->toBe('camille@example.com')
        ->and($fresh->isAnonymized())->toBeTrue();

    // L'inscription (justificatif comptable) est conservée.
    $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'student_id' => $student->id]);
});

it('efface la progression lors de l\'anonymisation', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);
    $student->lessonProgress()->create(['lesson_id' => $lesson->id, 'completed_at' => now()]);

    StudentAnonymizer::anonymize($student);

    $this->assertDatabaseMissing('lesson_progress', ['student_id' => $student->id]);
});

it('supprime le compte depuis le tableau de bord élève', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student')
        ->delete(route('student.account.destroy'))
        ->assertRedirect(route('courses.index'));

    expect($student->fresh()->isAnonymized())->toBeTrue();
    expect(auth('student')->check())->toBeFalse();
});
