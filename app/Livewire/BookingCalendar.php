<?php

namespace App\Livewire;

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentNotification;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\AppointmentSlotService;
use App\Support\Settings;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingCalendar extends Component
{
    #[Locked]
    public int $serviceId;

    public int $year;

    public int $month;

    public ?string $selectedDate = null;

    public ?string $selectedSlot = null;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $channel = AppointmentChannel::Video->value;

    public string $notes = '';

    public bool $consent = false;

    public string $website = '';

    #[Locked]
    public ?string $rescheduleToken = null;

    public function mount(AppointmentService $service, ?string $rescheduleToken = null): void
    {
        $this->serviceId = $service->id;
        $this->rescheduleToken = $rescheduleToken;
        $now = CarbonImmutable::now();
        $this->year = $now->year;
        $this->month = $now->month;

        $this->jumpToFirstAvailableMonth($service);
    }

    #[Computed]
    public function isRescheduling(): bool
    {
        return $this->rescheduleToken !== null;
    }

    /**
     * Ouvre le calendrier directement sur le premier mois ayant au moins un créneau,
     * pour que le visiteur ne tombe jamais sur une grille vide. Borné par l'horizon
     * de réservation.
     */
    private function jumpToFirstAvailableMonth(AppointmentService $service): void
    {
        $cursor = CarbonImmutable::create($this->year, $this->month, 1);
        $limit = CarbonImmutable::now()->addDays($service->max_advance_days)->startOfMonth();

        while ($cursor->lessThanOrEqualTo($limit)) {
            if (! empty($this->slotService()->availableDaysForMonth($service, $cursor->year, $cursor->month))) {
                $this->year = $cursor->year;
                $this->month = $cursor->month;

                return;
            }

            $cursor = $cursor->addMonth();
        }
    }

    #[Computed]
    public function service(): AppointmentService
    {
        return AppointmentService::query()->findOrFail($this->serviceId);
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function availableDays(): array
    {
        return $this->slotService()
            ->availableDaysForMonth($this->service, $this->year, $this->month);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    #[Computed]
    public function slots(): array
    {
        if ($this->selectedDate === null) {
            return [];
        }

        return $this->slotService()
            ->slotsForDate($this->service, CarbonImmutable::parse($this->selectedDate))
            ->map(fn (array $slot) => [
                'value' => $slot['start']->toIso8601String(),
                'label' => $slot['label'],
            ])
            ->all();
    }

    public function previousMonth(): void
    {
        $cursor = CarbonImmutable::create($this->year, $this->month, 1)->subMonth();
        $this->year = $cursor->year;
        $this->month = $cursor->month;
        $this->resetSelection();
    }

    public function nextMonth(): void
    {
        $cursor = CarbonImmutable::create($this->year, $this->month, 1)->addMonth();
        $this->year = $cursor->year;
        $this->month = $cursor->month;
        $this->resetSelection();
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedSlot = null;
    }

    public function selectSlot(string $slot): void
    {
        $this->selectedSlot = $slot;
    }

    public function book(): mixed
    {
        if ($this->isRescheduling) {
            return $this->reschedule();
        }

        $this->validate([
            'firstName' => ['required', 'string', 'max:80'],
            'lastName' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'channel' => ['required', Rule::enum(AppointmentChannel::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'selectedSlot' => ['required', 'string'],
            'website' => ['prohibited'],
        ], [
            'firstName.required' => 'Votre prénom est requis.',
            'email.required' => 'Votre email est requis.',
            'email.email' => 'Cet email n\'est pas valide.',
            'channel.required' => 'Veuillez choisir le format du rendez-vous.',
            'consent.accepted' => 'Vous devez accepter le traitement de vos données.',
            'selectedSlot.required' => 'Veuillez choisir un créneau.',
            'website.prohibited' => 'Erreur de soumission.',
        ]);

        $start = $this->guardedSlotStart();
        if ($start === null) {
            return null;
        }

        $service = $this->service;
        $isPaid = $service->price_cents > 0;

        $appointment = $this->slotService()->reserve($service, $start, [
            'reference' => Appointment::generateReference(),
            'token' => Appointment::generateToken(),
            'customer_first_name' => $this->firstName,
            'customer_last_name' => $this->lastName ?: null,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone ?: null,
            'channel' => $this->channel,
            'notes' => $this->notes ?: null,
            'meeting_url' => Settings::get('meet_url') ?: null,
            'status' => ($isPaid || $service->requires_confirmation) ? AppointmentStatus::Pending : AppointmentStatus::Confirmed,
            'price_cents' => $service->price_cents,
            'payment_status' => $isPaid ? PaymentStatus::Unpaid : PaymentStatus::NotRequired,
        ]);

        if ($appointment === null) {
            $this->selectedSlot = null;
            $this->addError('selectedSlot', 'Ce créneau vient d\'être réservé. Merci d\'en choisir un autre.');

            return null;
        }

        if ($isPaid) {
            return $this->redirect(route('booking.pay', $appointment->token), navigate: false);
        }

        Mail::to($appointment->customer_email)->send(new AppointmentConfirmation($appointment));
        Mail::to(Settings::get('notify_email', config('mail.contact_to', 'contact@vivre-pleinement.fr')))
            ->send(new AppointmentNotification($appointment));

        return redirect()->route('booking.confirmation', $appointment->reference);
    }

    private function reschedule(): mixed
    {
        $this->validate(['selectedSlot' => ['required', 'string']], [
            'selectedSlot.required' => 'Veuillez choisir un nouveau créneau.',
        ]);

        $appointment = Appointment::query()->where('token', $this->rescheduleToken)->firstOrFail();

        if (! $appointment->isManageable()) {
            $this->addError('selectedSlot', 'Ce rendez-vous ne peut plus être reprogrammé.');

            return null;
        }

        $start = $this->guardedSlotStart();
        if ($start === null) {
            return null;
        }

        $previousStart = $appointment->starts_at->copy();

        if (! $this->slotService()->move($appointment, $start)) {
            $this->selectedSlot = null;
            $this->addError('selectedSlot', 'Ce créneau vient d\'être réservé. Merci d\'en choisir un autre.');

            return null;
        }

        $appointment = $appointment->fresh('service');

        Mail::to($appointment->customer_email)->send(new AppointmentRescheduled($appointment, $previousStart));
        Mail::to(Settings::get('notify_email', config('mail.contact_to', 'contact@vivre-pleinement.fr')))
            ->send(new AppointmentRescheduled($appointment, $previousStart, forAdmin: true));

        return redirect()->route('booking.manage', $appointment->token);
    }

    /**
     * Applique la limitation de tentatives et revérifie que le créneau choisi est
     * réellement libre. Renvoie l'instant de début, ou null après avoir posé une
     * erreur (l'appelant doit alors interrompre).
     */
    private function guardedSlotStart(): ?CarbonImmutable
    {
        $key = 'booking:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('selectedSlot', 'Trop de tentatives. Réessayez dans quelques minutes.');

            return null;
        }

        $start = CarbonImmutable::parse($this->selectedSlot);

        if (! $this->slotService()->isSlotBookable($this->service, $start)) {
            $this->selectedSlot = null;
            $this->addError('selectedSlot', 'Ce créneau vient d\'être réservé. Merci d\'en choisir un autre.');

            return null;
        }

        RateLimiter::hit($key, 600);

        return $start;
    }

    private function resetSelection(): void
    {
        $this->selectedDate = null;
        $this->selectedSlot = null;
    }

    private function slotService(): AppointmentSlotService
    {
        return app(AppointmentSlotService::class);
    }

    public function render(): View
    {
        return view('livewire.booking-calendar');
    }
}
