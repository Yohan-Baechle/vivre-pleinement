<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Models\DateOverride;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AppointmentSlotService
{
    /**
     * Liste les dates d'un mois donné qui ont au moins un créneau réservable.
     *
     * @return array<int, string> dates ISO (Y-m-d)
     */
    public function availableDaysForMonth(AppointmentService $service, int $year, int $month): array
    {
        $first = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $last = $first->endOfMonth();

        $days = [];

        for ($date = $first; $date->lessThanOrEqualTo($last); $date = $date->addDay()) {
            if ($this->slotsForDate($service, $date)->isNotEmpty()) {
                $days[] = $date->format('Y-m-d');
            }
        }

        return $days;
    }

    /**
     * Liste les créneaux réservables pour une date donnée.
     *
     * @return Collection<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>
     */
    public function slotsForDate(AppointmentService $service, CarbonImmutable $date): Collection
    {
        $date = $date->startOfDay();
        $now = CarbonImmutable::now();

        if ($date->greaterThan($now->addDays($service->max_advance_days)->endOfDay())) {
            return collect();
        }

        $overrides = $this->overridesForDate($date);
        if ($overrides->contains(fn (DateOverride $o) => $o->isFullDay())) {
            return collect();
        }

        $minBookable = $now->addHours($service->min_notice_hours);
        $booked = $this->bookedRangesForDate($service, $date);

        return $this->availabilitiesForDate($service, $date)
            ->flatMap(fn (Availability $availability) => $this->slotsFromAvailability($availability, $date, $service))
            ->reject(function (array $slot) use ($minBookable, $overrides, $booked) {
                if ($slot['start']->lessThan($minBookable)) {
                    return true;
                }

                foreach ($overrides as $override) {
                    if ($this->overlapsOverride($slot, $override, $slot['start'])) {
                        return true;
                    }
                }

                foreach ($booked as $range) {
                    if ($slot['start']->lessThan($range['end']) && $slot['end']->greaterThan($range['start'])) {
                        return true;
                    }
                }

                return false;
            })
            ->sortBy(fn (array $slot) => $slot['start']->getTimestamp())
            ->values()
            ->map(fn (array $slot) => [
                'start' => $slot['start'],
                'end' => $slot['end'],
                'label' => $slot['start']->format('H:i'),
            ]);
    }

    /**
     * Vérifie côté serveur qu'un début de créneau précis est réellement réservable.
     */
    public function isSlotBookable(AppointmentService $service, CarbonImmutable $start): bool
    {
        return $this->slotsForDate($service, $start)
            ->contains(fn (array $slot) => $slot['start']->equalTo($start));
    }

    /**
     * Indique si un autre rendez-vous bloquant chevauche la plage horaire de celui-ci.
     * Sert à détecter un créneau pris pendant le tunnel de paiement.
     */
    public function hasConflictingAppointment(Appointment $appointment): bool
    {
        return $this->overlapQuery($appointment->appointment_service_id, $appointment->starts_at, $appointment->ends_at)
            ->where('id', '!=', $appointment->id)
            ->exists();
    }

    /**
     * Crée un rendez-vous pour un créneau de façon atomique, en se protégeant
     * de la double-réservation concurrente via un verrou de ligne et une
     * revérification du conflit dans la transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function reserve(AppointmentService $service, CarbonImmutable $start, array $attributes): ?Appointment
    {
        $end = $start->addMinutes($service->duration_minutes);

        return DB::transaction(function () use ($service, $start, $end, $attributes) {
            AppointmentService::query()->whereKey($service->id)->lockForUpdate()->first();

            if ($this->overlapQuery($service->id, $start, $end)->lockForUpdate()->exists()) {
                return null;
            }

            return Appointment::create(array_merge($attributes, [
                'appointment_service_id' => $service->id,
                'starts_at' => $start,
                'ends_at' => $end,
            ]));
        });
    }

    /**
     * Déplace de façon atomique un rendez-vous existant vers un nouveau créneau.
     */
    public function move(Appointment $appointment, CarbonImmutable $start): bool
    {
        $service = $appointment->service;
        $end = $start->addMinutes($service->duration_minutes);

        return DB::transaction(function () use ($appointment, $service, $start, $end) {
            AppointmentService::query()->whereKey($service->id)->lockForUpdate()->first();

            $conflict = $this->overlapQuery($service->id, $start, $end)
                ->where('id', '!=', $appointment->id)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                return false;
            }

            $appointment->update(['starts_at' => $start, 'ends_at' => $end]);

            return true;
        });
    }

    /**
     * @return Builder<Appointment>
     */
    private function overlapQuery(int $serviceId, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return Appointment::query()
            ->where('appointment_service_id', $serviceId)
            ->blocking()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start);
    }

    /**
     * @return Collection<int, Availability>
     */
    private function availabilitiesForDate(AppointmentService $service, CarbonImmutable $date): Collection
    {
        return Availability::query()
            ->where('is_active', true)
            ->where('day_of_week', $date->dayOfWeek)
            ->where(function ($query) use ($service) {
                $query->whereNull('appointment_service_id')
                    ->orWhere('appointment_service_id', $service->id);
            })
            ->get();
    }

    /**
     * @return Collection<int, DateOverride>
     */
    private function overridesForDate(CarbonImmutable $date): Collection
    {
        return DateOverride::query()
            ->whereDate('date', $date->toDateString())
            ->get();
    }

    /**
     * @return Collection<int, array{start: CarbonImmutable, end: CarbonImmutable}>
     */
    private function bookedRangesForDate(AppointmentService $service, CarbonImmutable $date): Collection
    {
        return Appointment::query()
            ->where('appointment_service_id', $service->id)
            ->blocking()
            ->whereDate('starts_at', $date->toDateString())
            ->get(['starts_at', 'ends_at'])
            ->map(fn (Appointment $appointment) => [
                'start' => CarbonImmutable::parse($appointment->starts_at),
                'end' => CarbonImmutable::parse($appointment->ends_at),
            ]);
    }

    /**
     * Découpe une fenêtre de disponibilité en créneaux consécutifs selon la durée du service.
     *
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable}>
     */
    private function slotsFromAvailability(Availability $availability, CarbonImmutable $date, AppointmentService $service): array
    {
        $windowStart = $this->applyTime($date, $availability->start_time);
        $windowEnd = $this->applyTime($date, $availability->end_time);
        $step = max(1, $service->duration_minutes + $service->buffer_minutes);

        $slots = [];

        for ($start = $windowStart; true; $start = $start->addMinutes($step)) {
            $end = $start->addMinutes($service->duration_minutes);

            if ($end->greaterThan($windowEnd)) {
                break;
            }

            $slots[] = ['start' => $start, 'end' => $end];
        }

        return $slots;
    }

    private function overlapsOverride(array $slot, DateOverride $override, CarbonImmutable $date): bool
    {
        if ($override->isFullDay()) {
            return true;
        }

        $blockStart = $this->applyTime($date, $override->start_time);
        $blockEnd = $this->applyTime($date, $override->end_time);

        return $slot['start']->lessThan($blockEnd) && $slot['end']->greaterThan($blockStart);
    }

    private function applyTime(CarbonImmutable $date, string $time): CarbonImmutable
    {
        [$hour, $minute] = array_pad(explode(':', $time), 2, '0');

        return $date->setTime((int) $hour, (int) $minute);
    }
}
