<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\BookingPaymentService;
use App\Support\IcsCalendar;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $services = AppointmentService::query()->active()->orderBy('sort_order')->get();

        return view('booking.index', [
            'services' => $services,
            'primaryService' => $services->first(),
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
     * On-site payment page (Stripe Payment Element) for a payable appointment.
     */
    public function pay(Appointment $appointment, BookingPaymentService $payments): View|RedirectResponse
    {
        $appointment->load('service');

        if ($appointment->payment_status === PaymentStatus::Paid) {
            return redirect()->route('booking.confirmation', $appointment->reference);
        }

        abort_unless($appointment->price_cents > 0 && $appointment->isManageable(), 404);

        $intent = $payments->createPaymentIntent($appointment);

        return view('booking.pay', [
            'appointment' => $appointment,
            'clientSecret' => $intent->client_secret,
            'stripeKey' => config('cashier.key'),
        ]);
    }

    /**
     * Payment was abandoned: keep the (unpaid) appointment but inform the visitor.
     */
    public function paymentCancelled(Appointment $appointment): View
    {
        $appointment->load('service');

        return view('booking.payment-cancelled', ['appointment' => $appointment]);
    }

    /**
     * Self-service page (via secure token) to view, cancel or reschedule.
     */
    public function manage(Appointment $appointment): View
    {
        $appointment->load('service');

        return view('booking.manage', ['appointment' => $appointment]);
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->isManageable(), 403, 'Ce rendez-vous ne peut plus être annulé.');

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_at' => CarbonImmutable::now(),
        ]);

        Mail::to($appointment->customer_email)->send(new AppointmentCancelled($appointment));
        Mail::to(Settings::get('notify_email', config('mail.contact_to', 'contact@vivre-pleinement.fr')))
            ->send(new AppointmentCancelled($appointment, forAdmin: true));

        return redirect()->route('booking.manage', $appointment->token);
    }

    /**
     * Reschedule: reuse the booking calendar in "report" mode for this appointment.
     */
    public function reschedule(Appointment $appointment): View
    {
        abort_unless($appointment->isManageable(), 403, 'Ce rendez-vous ne peut plus être reprogrammé.');

        $appointment->load('service');

        return view('booking.reschedule', ['appointment' => $appointment]);
    }

    /**
     * Download an iCalendar (.ics) file for the appointment so the visitor
     * can add it to any calendar app – a simple lever against no-shows.
     */
    public function ics(Appointment $appointment): Response
    {
        $appointment->load('service');

        return response(IcsCalendar::forAppointment($appointment))
            ->header('Content-Type', 'text/calendar; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="rendez-vous-'.$appointment->reference.'.ics"');
    }
}
