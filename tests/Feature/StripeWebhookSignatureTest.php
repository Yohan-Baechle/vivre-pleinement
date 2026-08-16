<?php

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(LazilyRefreshDatabase::class);

/**
 * Cashier n'attache VerifyWebhookSignature que si cashier.webhook.secret est
 * renseigné. Sans ce garde-fou, /stripe/webhook devient un endpoint anonyme
 * capable d'activer une inscription payante.
 */
it('refuses cashier requests when the webhook secret is not configured', function () {
    config(['cashier.webhook.secret' => null]);

    $this->postJson('/stripe/webhook', ['type' => 'payment_intent.succeeded'])
        ->assertForbidden();
});

it('does not fulfill an enrollment from an unsigned webhook when the secret is missing', function () {
    config(['cashier.webhook.secret' => null]);

    $student = Student::factory()->create();
    $course = Course::factory()->create();
    $enrollment = Enrollment::factory()->create([
        'student_id' => $student->id,
        'course_id' => $course->id,
        'status' => EnrollmentStatus::Pending,
    ]);

    $this->postJson('/stripe/webhook', [
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => ['id' => 'pi_forged', 'metadata' => ['enrollment_id' => $enrollment->id]]],
    ])->assertForbidden();

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Pending);
});

it('lets the request through to Cashier once a webhook secret is configured', function () {
    Log::spy();
    config(['cashier.webhook.secret' => 'whsec_test']);

    $this->postJson('/stripe/webhook', ['type' => 'payment_intent.succeeded'])
        ->assertForbidden();

    Log::shouldNotHaveReceived('critical');
});

it('logs the refusal so a misconfigured deployment is noticed', function () {
    Log::spy();
    config(['cashier.webhook.secret' => null]);

    $this->postJson('/stripe/webhook', ['type' => 'payment_intent.succeeded'])
        ->assertForbidden();

    Log::shouldHaveReceived('critical')->once();
});

it('never blocks ordinary site traffic', function () {
    config(['cashier.webhook.secret' => null]);

    $this->get('/')->assertOk();
});
