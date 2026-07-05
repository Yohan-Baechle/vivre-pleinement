<?php

use App\Enums\EnrollmentStatus;
use App\Filament\Admin\Resources\Enrollments\Pages\ListEnrollments;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function activeEnrollment(string $intentId = 'pi_refund_test'): Enrollment
{
    return Enrollment::factory()->create([
        'student_id' => Student::factory()->create()->id,
        'course_id' => Course::factory()->create()->id,
        'stripe_payment_intent_id' => $intentId,
    ]);
}

function refundWebhook(?string $intentId = 'pi_refund_test'): void
{
    event(new WebhookReceived([
        'type' => 'charge.refunded',
        'data' => ['object' => [
            'id' => 'ch_test_123',
            'payment_intent' => $intentId,
        ]],
    ]));
}

it('révoque l\'accès sur un webhook charge.refunded', function () {
    $enrollment = activeEnrollment();

    refundWebhook();

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Refunded);
});

it('retire l\'accès aux leçons à un élève remboursé', function () {
    $enrollment = activeEnrollment();

    refundWebhook();

    expect($enrollment->student->fresh()->hasAccessTo($enrollment->course))->toBeFalse();
});

it('ignore un remboursement dont le PaymentIntent est inconnu', function () {
    $enrollment = activeEnrollment();

    refundWebhook('pi_autre_paiement');

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Active);
});

it('ignore un webhook charge.refunded sans payment_intent', function () {
    $enrollment = activeEnrollment();

    refundWebhook(null);

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Active);
});

it('ne réactive pas une inscription en attente lors d\'un remboursement', function () {
    $enrollment = Enrollment::factory()->pending()->create([
        'stripe_payment_intent_id' => 'pi_refund_test',
    ]);

    refundWebhook();

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Pending);
});

it('permet à un admin de marquer une inscription comme remboursée', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $enrollment = activeEnrollment();

    Livewire::test(ListEnrollments::class)
        ->callAction(TestAction::make('markRefunded')->table($enrollment))
        ->assertHasNoActionErrors();

    expect($enrollment->fresh()->status)->toBe(EnrollmentStatus::Refunded);
});
