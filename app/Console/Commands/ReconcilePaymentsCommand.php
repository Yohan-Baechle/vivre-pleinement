<?php

namespace App\Console\Commands;

use App\Enums\BookOrderStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\BookOrder;
use App\Models\Enrollment;
use App\Services\BookingPaymentService;
use App\Services\BookPaymentService;
use App\Services\CoursePaymentService;
use App\Services\StripePaymentIntents;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Throwable;

/**
 * Rattrape les paiements encaissés par Stripe restés sans effet dans
 * l'application.
 *
 * Le webhook est le chemin normal, celui-ci est le filet. Un webhook peut se
 * perdre : worker arrêté, secret de signature erroné, job épuisant ses
 * tentatives, indisponibilité au mauvais moment. Le symptôme est alors
 * silencieux et coûteux, le client est débité et ne reçoit rien, sa page
 * tourne indéfiniment, et rien ne le signale.
 *
 * Stripe reste la source de vérité : on n'accorde jamais un accès sans avoir
 * confirmé auprès de lui que le PaymentIntent est bien au statut succeeded.
 */
#[Signature('payments:reconcile {--minutes=15 : Ancienneté minimale, en minutes, des enregistrements examinés}')]
#[Description('Finalise les paiements confirmés par Stripe dont le webhook ne nous est jamais parvenu.')]
class ReconcilePaymentsCommand extends Command
{
    public function __construct(
        private StripePaymentIntents $intents,
        private BookingPaymentService $bookingPayments,
        private CoursePaymentService $coursePayments,
        private BookPaymentService $bookPayments,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        /**
         * On laisse au webhook le temps d'arriver : sans ce délai, la commande
         * doublerait le chemin normal sur des paiements de quelques secondes.
         */
        $before = CarbonImmutable::now()->subMinutes((int) $this->option('minutes'));

        $recovered = $this->reconcileAppointments($before)
            + $this->reconcileEnrollments($before)
            + $this->reconcileBookOrders($before);

        $this->info($recovered === 0
            ? 'Aucun paiement en souffrance.'
            : "Paiements rattrapés : {$recovered}.");

        return self::SUCCESS;
    }

    private function reconcileAppointments(CarbonImmutable $before): int
    {
        $pending = Appointment::query()
            ->where('payment_status', PaymentStatus::Unpaid)
            ->whereNotNull('stripe_payment_intent_id')
            ->where('created_at', '<=', $before)
            ->get();

        return $this->recover(
            $pending,
            'rendez-vous',
            fn (Appointment $a, PaymentIntent $intent) => $this->bookingPayments->fulfill(
                $a,
                $intent->id,
            ),
        );
    }

    private function reconcileEnrollments(CarbonImmutable $before): int
    {
        $pending = Enrollment::query()
            ->where('status', EnrollmentStatus::Pending)
            ->whereNotNull('stripe_payment_intent_id')
            ->where('created_at', '<=', $before)
            ->get();

        return $this->recover(
            $pending,
            'inscription',
            fn (Enrollment $e, PaymentIntent $intent) => $this->coursePayments->fulfill(
                $e,
                $intent->id,
                $this->amountReceived($intent),
                $this->currency($intent),
            ),
        );
    }

    private function reconcileBookOrders(CarbonImmutable $before): int
    {
        $pending = BookOrder::query()
            ->where('status', BookOrderStatus::Pending)
            ->whereNotNull('stripe_payment_intent_id')
            ->where('created_at', '<=', $before)
            ->get();

        return $this->recover(
            $pending,
            'commande livre',
            fn (BookOrder $o, PaymentIntent $intent) => $this->bookPayments->fulfill(
                $o,
                $intent->id,
                $this->amountReceived($intent),
                $this->currency($intent),
            ),
        );
    }

    /**
     * Le montant réellement encaissé prime sur le prix courant : c'est ce que
     * le client a payé, pas ce que le catalogue affiche aujourd'hui.
     */
    private function amountReceived(PaymentIntent $intent): ?int
    {
        return is_int($intent->amount_received) ? $intent->amount_received : null;
    }

    private function currency(PaymentIntent $intent): ?string
    {
        return is_string($intent->currency) ? $intent->currency : null;
    }

    /**
     * Confronte chaque enregistrement à Stripe et finalise ceux qui ont
     * réellement été payés.
     *
     * Un échec sur une ligne n'interrompt pas les suivantes : la commande est
     * planifiée, et laisser un rattrapage bloquer tous les autres serait pire
     * que le problème traité.
     *
     * @param  Collection<int, Model>  $records
     * @param  callable(mixed, PaymentIntent): void  $fulfill
     */
    private function recover(Collection $records, string $label, callable $fulfill): int
    {
        $recovered = 0;

        foreach ($records as $record) {
            $intent = $this->intents->retrieve($record->stripe_payment_intent_id);

            if ($intent === null || $intent->status !== 'succeeded') {
                continue;
            }

            try {
                $fulfill($record, $intent);
            } catch (Throwable $exception) {
                report($exception);

                Log::error("Rattrapage impossible pour un {$label} payé.", [
                    'id' => $record->getKey(),
                    'payment_intent_id' => $record->stripe_payment_intent_id,
                    'exception' => $exception->getMessage(),
                ]);

                continue;
            }

            $recovered++;

            /**
             * En warning et non en info : chaque ligne rattrapée signale un
             * webhook perdu, donc une anomalie à comprendre, pas une routine.
             */
            Log::warning("Paiement rattrapé sans webhook : {$label}.", [
                'id' => $record->getKey(),
                'payment_intent_id' => $record->stripe_payment_intent_id,
            ]);

            $this->warn("Rattrapé : {$label} #{$record->getKey()}");
        }

        return $recovered;
    }
}
