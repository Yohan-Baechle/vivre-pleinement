<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Mail\CourseAccessGranted;
use App\Mail\CoursePurchaseNotification;
use App\Models\Enrollment;
use App\Support\Settings;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use Stripe\PaymentIntent;

class CoursePaymentService
{
    /**
     * Crée un PaymentIntent Stripe pour l'achat d'une formation. Le client_secret
     * retourné alimente le Payment Element affiché sur le site. L'inscription est
     * activée plus tard par le webhook payment_intent.succeeded.
     */
    public function createPaymentIntent(Enrollment $enrollment): PaymentIntent
    {
        $enrollment->loadMissing(['course', 'student']);

        $customer = $enrollment->student->createOrGetStripeCustomer();

        return Cashier::stripe()->paymentIntents->create([
            'amount' => $enrollment->course->price_cents,
            'currency' => config('cashier.currency', 'eur'),
            'customer' => $customer->id,
            'description' => 'Formation : '.$enrollment->course->title,
            'receipt_email' => $enrollment->student->email,
            'metadata' => [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'student_id' => $enrollment->student_id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }

    /**
     * Active l'inscription après paiement réussi, puis notifie l'élève et l'admin.
     * Idempotent : un webhook dupliqué pour une inscription déjà active ne fait rien.
     */
    public function fulfill(Enrollment $enrollment, ?string $paymentIntentId = null): void
    {
        if ($enrollment->status === EnrollmentStatus::Active) {
            return;
        }

        $enrollment->loadMissing(['course', 'student']);

        $enrollment->update([
            'status' => EnrollmentStatus::Active,
            'amount_paid_cents' => $enrollment->course->price_cents,
            'currency' => $enrollment->course->currency,
            'stripe_payment_intent_id' => $paymentIntentId,
            'purchased_at' => now(),
        ]);

        $fresh = $enrollment->fresh(['course', 'student']);

        Mail::to($enrollment->student->email)->send(new CourseAccessGranted($fresh));
        Mail::to(Settings::get('notify_email', config('mail.contact_to', 'contact@vivre-pleinement.fr')))
            ->send(new CoursePurchaseNotification($fresh));
    }
}
