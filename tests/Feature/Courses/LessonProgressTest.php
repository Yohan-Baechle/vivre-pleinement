<?php

use App\Livewire\Student\LessonPlayer;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Support\CourseProgress;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

/**
 * @return array{0: Course, 1: array<int, Lesson>, 2: Student}
 */
function enrolledCourse(int $lessons = 2): array
{
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $lessonModels = Lesson::factory()->count($lessons)->create(['module_id' => $module->id]);
    $student = Student::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

    return [$course, $lessonModels->all(), $student];
}

it('marque une leçon comme terminée', function () {
    [$course, $lessons, $student] = enrolledCourse();

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessons[0]])
        ->call('markComplete')
        ->assertSet('completed', true);

    $this->assertDatabaseHas('lesson_progress', [
        'student_id' => $student->id,
        'lesson_id' => $lessons[0]->id,
    ]);
});

it('est idempotent : marquer deux fois ne crée qu\'une ligne', function () {
    [$course, $lessons, $student] = enrolledCourse();

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessons[0]])
        ->call('markComplete')
        ->call('markComplete');

    expect($student->lessonProgress()->where('lesson_id', $lessons[0]->id)->count())->toBe(1);
});

it('calcule correctement le pourcentage de progression', function () {
    [$course, $lessons, $student] = enrolledCourse(lessons: 2);

    expect(CourseProgress::percent($student, $course))->toBe(0);

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessons[0]])
        ->call('markComplete');

    expect(CourseProgress::percent($student, $course))->toBe(50);
});

it('calcule la progression de plusieurs formations en une seule passe agrégée', function () {
    [$courseA, $lessonsA, $student] = enrolledCourse(lessons: 2);
    $courseB = Course::factory()->create();
    $moduleB = Module::factory()->create(['course_id' => $courseB->id]);
    $lessonsB = Lesson::factory()->count(4)->create(['module_id' => $moduleB->id]);
    Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $courseB->id]);

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $courseA, 'lesson' => $lessonsA[0]])
        ->call('markComplete');

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $courseB, 'lesson' => $lessonsB[0]])
        ->call('markComplete');

    $progress = CourseProgress::percentForCourses($student, collect([$courseA, $courseB]));

    expect($progress[$courseA->id])->toBe(50)
        ->and($progress[$courseB->id])->toBe(25);
});

it('shows the correct per-course progress on the student dashboard', function () {
    [$course, $lessons, $student] = enrolledCourse(lessons: 4);

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessons[0]])
        ->call('markComplete');

    $this->actingAs($student, 'student')
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertViewHas('progress', fn ($progress) => $progress[$course->id] === 25);
});

it('permet de marquer une leçon comme non terminée', function () {
    [$course, $lessons, $student] = enrolledCourse();

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessons[0]])
        ->call('markComplete')
        ->call('markIncomplete')
        ->assertSet('completed', false);

    $this->assertDatabaseMissing('lesson_progress', [
        'student_id' => $student->id,
        'lesson_id' => $lessons[0]->id,
    ]);
});

it('ne recharge pas deux fois les leçons de la formation lors du rendu (cache #[Computed])', function () {
    [$course, $lessons, $student] = enrolledCourse(lessons: 3);

    DB::enableQueryLog();

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lessons[1]])
        ->assertOk();

    $log = DB::getQueryLog();
    DB::disableQueryLog();

    $modulesQueries = collect($log)->filter(fn ($q) => str_contains($q['query'], 'from `modules`') || str_contains($q['query'], 'from "modules"'));

    expect($modulesQueries)->toHaveCount(1);
});

it('empêche un élève non inscrit de valider une leçon', function () {
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id]);
    $lesson = Lesson::factory()->create(['module_id' => $module->id]);
    $student = Student::factory()->create();

    Livewire::actingAs($student, 'student')
        ->test(LessonPlayer::class, ['course' => $course, 'lesson' => $lesson])
        ->call('markComplete')
        ->assertForbidden();

    $this->assertDatabaseCount('lesson_progress', 0);
});
