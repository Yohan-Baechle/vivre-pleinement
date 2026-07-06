<?php

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\CoursePaymentService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Stripe\Exception\ApiConnectionException;
use Stripe\PaymentIntent;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    // Évite tout appel réseau vers Stripe : on simule un PaymentIntent.
    $intent = PaymentIntent::constructFrom(['id' => 'pi_test', 'client_secret' => 'pi_secret_test']);

    $this->mock(CoursePaymentService::class)
        ->shouldReceive('getOrCreatePaymentIntent')
        ->andReturn($intent);
});

it('crée une inscription en attente puis redirige vers le paiement', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($student, 'student')
        ->post(route('courses.checkout.start', $course))
        ->assertRedirect(route('courses.checkout.pay', $course));

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'course_id' => $course->id,
        'status' => EnrollmentStatus::Pending->value,
    ]);
});

it('ne crée pas de double inscription pour la même formation', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();

    $this->actingAs($student, 'student')->post(route('courses.checkout.start', $course));
    $this->actingAs($student, 'student')->post(route('courses.checkout.start', $course));

    expect(Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->count())->toBe(1);
});

it('affiche la page de paiement avec le client secret', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();

    Enrollment::factory()->pending()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $this->actingAs($student, 'student')
        ->get(route('courses.checkout.pay', $course))
        ->assertOk()
        ->assertSee('pi_secret_test', false);
});

it('redirige vers la formation si l\'élève y a déjà accès', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();
    Enrollment::factory()->create(['student_id' => $student->id, 'course_id' => $course->id]);

    $this->actingAs($student, 'student')
        ->post(route('courses.checkout.start', $course))
        ->assertRedirect(route('student.course', $course));
});

it('exige une authentification élève pour acheter', function () {
    $course = Course::factory()->create();

    $this->post(route('courses.checkout.start', $course))
        ->assertRedirect(route('student.login'));
});

it('retourne un 503 au lieu d\'un 500 brut quand Stripe est indisponible', function () {
    $student = Student::factory()->create();
    $course = Course::factory()->create();

    Enrollment::factory()->pending()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
    ]);

    $this->mock(CoursePaymentService::class)
        ->shouldReceive('getOrCreatePaymentIntent')
        ->once()
        ->andThrow(new ApiConnectionException('Stripe est indisponible'));

    $this->actingAs($student, 'student')
        ->get(route('courses.checkout.pay', $course))
        ->assertStatus(503);
});
