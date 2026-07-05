<?php

use App\Enums\EnrollmentStatus;
use App\Mail\CourseAccessGranted;
use App\Mail\CoursePurchaseNotification;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

uses(RefreshDatabase::class);

function pendingEnrollment(): Enrollment
{
    $student = Student::factory()->create();
    $course = Course::factory()->create(['price_cents' => 14900]);

    return Enrollment::factory()->pending()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
    ]);
}

function courseWebhook(int $enrollmentId, string $intentId = 'pi_course_123'): void
{
    event(new WebhookReceived([
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => $intentId,
            'metadata' => ['enrollment_id' => $enrollmentId],
        ]],
    ]));
}

it('active l\'inscription sur un webhook payment_intent.succeeded', function () {
    Mail::fake();
    $enrollment = pendingEnrollment();

    courseWebhook($enrollment->id);

    $fresh = $enrollment->fresh();
    expect($fresh->status)->toBe(EnrollmentStatus::Active)
        ->and($fresh->amount_paid_cents)->toBe(14900)
        ->and($fresh->stripe_payment_intent_id)->toBe('pi_course_123')
        ->and($fresh->purchased_at)->not->toBeNull();

    Mail::assertQueued(CourseAccessGranted::class);
    Mail::assertQueued(CoursePurchaseNotification::class);
});

it('est idempotent : un webhook dupliqué ne renvoie pas un second email', function () {
    Mail::fake();
    $enrollment = pendingEnrollment();

    courseWebhook($enrollment->id);
    courseWebhook($enrollment->id);

    Mail::assertQueued(CourseAccessGranted::class, 1);
});

it('ignore un webhook d\'un autre type', function () {
    Mail::fake();
    $enrollment = pendingEnrollment();

    event(new WebhookReceived([
        'type' => 'payment_intent.created',
        'data' => ['object' => ['id' => 'pi_x', 'metadata' => ['enrollment_id' => $enrollment->id]]],
    ]));

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Pending);
    Mail::assertNothingQueued();
});

it('ne touche pas aux inscriptions lorsqu\'un webhook concerne un rendez-vous', function () {
    Mail::fake();
    $enrollment = pendingEnrollment();

    event(new WebhookReceived([
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => [
            'id' => 'pi_appointment',
            'metadata' => ['appointment_id' => 999999],
        ]],
    ]));

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Pending);
    Mail::assertNotQueued(CourseAccessGranted::class);
});
