<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Mail\CourseAccessGranted;
use App\Mail\CoursePurchaseNotification;
use App\Models\Enrollment;
use App\Models\Student;
use App\Support\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use Stripe\PaymentIntent;
use Throwable;

class CoursePaymentService
{
    /**
     * Statuts Stripe pour lesquels un PaymentIntent existant peut resservir au
     * Payment Element au lieu d'en créer un nouveau (risque de double débit sinon).
     *
     * @var list<string>
     */
    private const REUSABLE_INTENT_STATUSES = [
        'requires_payment_method',
        'requires_confirmation',
        'requires_action',
        'processing',
    ];

    /**
     * Retourne le PaymentIntent de l'inscription : réutilise celui déjà créé
     * (rafraîchissement, second onglet) tant qu'il n'est pas finalisé, sinon en
     * crée un nouveau. Le client_secret retourné alimente le Payment Element.
     * L'inscription est activée plus tard par le webhook payment_intent.succeeded.
     */
    public function getOrCreatePaymentIntent(Enrollment $enrollment): PaymentIntent
    {
        $enrollment->loadMissing(['course', 'student']);

        $existing = $this->reusableIntentFor($enrollment);

        if ($existing !== null) {
            return $existing;
        }

        $intent = $this->createIntent([
            'amount' => $enrollment->course->price_cents,
            'currency' => strtolower($enrollment->course->currency ?? config('cashier.currency', 'eur')),
            'customer' => $this->resolveStripeCustomerId($enrollment->student),
            'description' => 'Formation : '.$enrollment->course->title,
            'receipt_email' => $enrollment->student->email,
            'metadata' => [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'student_id' => $enrollment->student_id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $enrollment->update(['stripe_payment_intent_id' => $intent->id]);

        return $intent;
    }

    /**
     * Récupère le PaymentIntent déjà rattaché à l'inscription s'il est encore
     * utilisable, en réalignant son montant si le prix de la formation a changé
     * entre-temps.
     */
    private function reusableIntentFor(Enrollment $enrollment): ?PaymentIntent
    {
        if ($enrollment->stripe_payment_intent_id === null) {
            return null;
        }

        $intent = $this->retrieveIntent($enrollment->stripe_payment_intent_id);

        if ($intent === null || ! in_array($intent->status, self::REUSABLE_INTENT_STATUSES, true)) {
            return null;
        }

        if ($intent->amount !== $enrollment->course->price_cents && str_starts_with($intent->status, 'requires_')) {
            return $this->updateIntentAmount($intent->id, $enrollment->course->price_cents);
        }

        return $intent;
    }

    public function retrieveIntent(string $paymentIntentId): ?PaymentIntent
    {
        try {
            return Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function createIntent(array $params): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->create($params);
    }

    public function updateIntentAmount(string $paymentIntentId, int $amountCents): PaymentIntent
    {
        return Cashier::stripe()->paymentIntents->update($paymentIntentId, ['amount' => $amountCents]);
    }

    public function resolveStripeCustomerId(Student $student): string
    {
        return $student->createOrGetStripeCustomer()->id;
    }

    /**
     * Active l'inscription après paiement réussi, puis notifie l'élève et l'admin.
     * Idempotent : un webhook dupliqué pour une inscription déjà active ne fait
     * rien ; un paiement concurrent (intent différent) est remboursé d'office.
     * Le montant et la devise enregistrés viennent du payload Stripe : c'est ce
     * qui a réellement été payé, pas le prix courant du cours. Le cours est
     * chargé withTrashed : un cours supprimé entre le paiement et le webhook ne
     * doit pas faire échouer le job, l'élève ayant déjà payé.
     */
    public function fulfill(
        Enrollment $enrollment,
        ?string $paymentIntentId = null,
        ?int $amountReceivedCents = null,
        ?string $currency = null,
    ): void {
        if ($enrollment->status === EnrollmentStatus::Active) {
            $this->refundDuplicatePayment($enrollment, $paymentIntentId);

            return;
        }

        $enrollment->loadMissing(['student', 'course' => fn ($query) => $query->withTrashed()]);

        $enrollment->update([
            'status' => EnrollmentStatus::Active,
            'amount_paid_cents' => $amountReceivedCents ?? $enrollment->course->price_cents,
            'currency' => $currency !== null ? strtoupper($currency) : $enrollment->course->currency,
            'stripe_payment_intent_id' => $paymentIntentId,
            'purchased_at' => now(),
        ]);

        $fresh = $enrollment->fresh(['student']);
        $fresh->setRelation('course', $enrollment->course);

        Mail::to($enrollment->student->email)->send(new CourseAccessGranted($fresh));
        Mail::to(Settings::get('notify_email', config('mail.contact_to', 'contact@vivre-pleinement.fr')))
            ->send(new CoursePurchaseNotification($fresh));
    }

    /**
     * Un paiement réussi arrive pour une inscription déjà active via un intent
     * différent : l'élève a payé deux fois (deux onglets avant la réutilisation
     * d'intent, ou course entre webhooks). On rembourse le second débit.
     */
    private function refundDuplicatePayment(Enrollment $enrollment, ?string $paymentIntentId): void
    {
        if ($paymentIntentId === null || $paymentIntentId === $enrollment->stripe_payment_intent_id) {
            return;
        }

        try {
            $this->refundPaymentIntent($paymentIntentId);

            Log::warning('Second paiement détecté pour une inscription déjà active : remboursé automatiquement.', [
                'enrollment_id' => $enrollment->id,
                'kept_payment_intent_id' => $enrollment->stripe_payment_intent_id,
                'refunded_payment_intent_id' => $paymentIntentId,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Second paiement détecté mais remboursement automatique impossible : à traiter dans le dashboard Stripe.', [
                'enrollment_id' => $enrollment->id,
                'payment_intent_id' => $paymentIntentId,
            ]);
        }
    }

    public function refundPaymentIntent(string $paymentIntentId): void
    {
        Cashier::stripe()->refunds->create(['payment_intent' => $paymentIntentId]);
    }

    /**
     * Révoque l'accès après un remboursement (webhook charge.refunded ou action
     * admin). Idempotent : une inscription déjà remboursée ou en attente ne bouge pas.
     */
    public function refund(Enrollment $enrollment): void
    {
        if ($enrollment->status !== EnrollmentStatus::Active) {
            return;
        }

        $enrollment->update(['status' => EnrollmentStatus::Refunded]);

        Log::info('Inscription remboursée, accès révoqué.', [
            'enrollment_id' => $enrollment->id,
            'course_id' => $enrollment->course_id,
            'student_id' => $enrollment->student_id,
        ]);
    }
}
