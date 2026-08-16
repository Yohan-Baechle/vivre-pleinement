<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\AppointmentLifecycleService;
use App\Services\AppointmentSlotService;
use App\Services\BookingPaymentService;
use App\Support\BookingFaq;
use App\Support\IcsCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class BookingController extends Controller
{
    public function index(AppointmentSlotService $slots): View
    {
        $services = AppointmentService::query()->active()->orderBy('sort_order')->get();
        $primaryService = $services->first();

        return view('booking.index', [
            'services' => $services,
            'primaryService' => $primaryService,
            'upcomingSlots' => $primaryService
                ? $slots->nextAvailableSlots($primaryService, 3)
                : collect(),
            'faq' => BookingFaq::all(),
        ]);
    }

    public function show(AppointmentService $service): View
    {
        abort_unless($service->is_active, 404);

        return view('booking.show', ['service' => $service]);
    }

    public function confirmation(Appointment $appointment): View
    {
        $appointment->load('service');

        return view('booking.confirmation', ['appointment' => $appointment]);
    }

    /**
     * Page de paiement sur le site (Stripe Payment Element) pour un
     * rendez-vous payant.
     */
    public function pay(Appointment $appointment, BookingPaymentService $payments): View|RedirectResponse
    {
        $appointment->load('service');

        if ($appointment->payment_status === PaymentStatus::Paid) {
            return redirect()->route('booking.confirmation', $appointment->token);
        }

        abort_unless($appointment->price_cents > 0 && $appointment->isManageable(), 404);

        try {
            $intent = $payments->createPaymentIntent($appointment);
        } catch (ApiErrorException $e) {
            report($e);

            abort(503, 'Le paiement est momentanément indisponible. Merci de réessayer dans quelques instants.');
        }

        return view('booking.pay', [
            'appointment' => $appointment,
            'clientSecret' => $intent->client_secret,
            'stripeKey' => config('cashier.key'),
        ]);
    }

    /**
     * Paiement abandonné : on conserve le rendez-vous (impayé) mais on en
     * informe le visiteur.
     */
    public function paymentCancelled(Appointment $appointment): View
    {
        $appointment->load('service');

        return view('booking.payment-cancelled', ['appointment' => $appointment]);
    }

    /**
     * Page d'autogestion (via le token secret) : consulter, annuler ou
     * reprogrammer le rendez-vous.
     */
    public function manage(Appointment $appointment): View
    {
        $appointment->load('service');

        return view('booking.manage', ['appointment' => $appointment]);
    }

    public function cancel(Appointment $appointment, AppointmentLifecycleService $lifecycle): RedirectResponse
    {
        abort_unless($appointment->isManageable(), 403, 'Ce rendez-vous ne peut plus être annulé.');

        $lifecycle->cancel($appointment);

        return redirect()->route('booking.manage', $appointment->token);
    }

    /**
     * Reprogrammation : réutilise le calendrier de réservation en mode
     * « report » pour ce rendez-vous.
     */
    public function reschedule(Appointment $appointment): View
    {
        abort_unless($appointment->isManageable(), 403, 'Ce rendez-vous ne peut plus être reprogrammé.');

        $appointment->load('service');

        return view('booking.reschedule', ['appointment' => $appointment]);
    }

    /**
     * Télécharge le rendez-vous au format iCalendar (.ics) pour que le
     * visiteur l'ajoute à son agenda — levier simple contre les absences.
     */
    public function ics(Appointment $appointment): Response
    {
        $appointment->load('service');

        return response(IcsCalendar::forAppointment($appointment))
            ->header('Content-Type', 'text/calendar; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="rendez-vous-'.$appointment->reference.'.ics"');
    }
}
