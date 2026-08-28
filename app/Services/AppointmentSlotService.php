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
        $context = $this->loadRangeContext($service, $first, $last);

        $days = [];

        for ($date = $first; $date->lessThanOrEqualTo($last); $date = $date->addDay()) {
            if ($this->slotsForDateInContext($service, $date, $context)->isNotEmpty()) {
                $days[] = $date->format('Y-m-d');
            }
        }

        return $days;
    }

    /**
     * Liste les créneaux réservables pour une date donnée.
     *
     * @return Collection<int, array{
     *     start: CarbonImmutable,
     *     end: CarbonImmutable,
     *     label: string,
     * }>
     */
    public function slotsForDate(AppointmentService $service, CarbonImmutable $date): Collection
    {
        $date = $date->startOfDay();

        return $this->slotsForDateInContext($service, $date, $this->loadRangeContext($service, $date, $date));
    }

    /**
     * Renvoie les prochains créneaux réservables, tous jours confondus, à
     * partir d'aujourd'hui et jusqu'à la limite de réservation anticipée du
     * service.
     *
     * @return Collection<int, array{
     *     start: CarbonImmutable,
     *     end: CarbonImmutable,
     *     label: string,
     * }>
     */
    public function nextAvailableSlots(AppointmentService $service, int $limit = 3): Collection
    {
        $found = collect();
        $date = CarbonImmutable::now()->startOfDay();
        $lastDay = $date->addDays($service->max_advance_days);
        $context = $this->loadRangeContext($service, $date, $lastDay);

        while ($date->lessThanOrEqualTo($lastDay) && $found->count() < $limit) {
            $found = $found->concat($this->slotsForDateInContext($service, $date, $context));
            $date = $date->addDay();
        }

        return $found->take($limit)->values();
    }

    /**
     * Charge en trois requêtes tout ce qu'il faut pour calculer les créneaux
     * d'une plage de dates : disponibilités actives, fermetures exceptionnelles
     * et rendez-vous bloquants, indexés par date. Évite les trois requêtes par
     * jour qui faisaient exploser le coût d'un mois de calendrier.
     *
     * @return array{
     *     availabilities: Collection<int, Availability>,
     *     overridesByDate: Collection<string, Collection<int, DateOverride>>,
     *     bookedByDate: Collection<string, Collection<int, Appointment>>
     * }
     */
    private function loadRangeContext(AppointmentService $service, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $availabilities = Availability::query()
            ->where('is_active', true)
            ->where(function ($query) use ($service) {
                $query->whereNull('appointment_service_id')
                    ->orWhere('appointment_service_id', $service->id);
            })
            ->get();

        $overridesByDate = DateOverride::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->groupBy(fn (DateOverride $override) => CarbonImmutable::parse($override->date)->toDateString());

        $bookedByDate = Appointment::query()
            ->where('appointment_service_id', $service->id)
            ->blocking()
            ->whereBetween('starts_at', [$from->startOfDay(), $to->endOfDay()])
            ->get(['starts_at', 'ends_at'])
            ->groupBy(fn (Appointment $appointment) => CarbonImmutable::parse($appointment->starts_at)->toDateString());

        return [
            'availabilities' => $availabilities,
            'overridesByDate' => $overridesByDate,
            'bookedByDate' => $bookedByDate,
        ];
    }

    /**
     * Calcule les créneaux d'une date à partir d'un contexte préchargé, sans
     * toucher à la base. Deux plages de disponibilité qui se chevauchent ne
     * doivent produire qu'un seul créneau par horaire de début, sans quoi le
     * client voit le même horaire proposé plusieurs fois.
     *
     * Le délai de prévenance est ramené à minuit du jour qu'il atteint : sans
     * cet arrondi, une consultation à 17 h 03 escamotait le créneau de 17 h du
     * jour ouvert par le délai, et la liste s'érodait heure par heure au fil de
     * la journée. La borne est malgré tout maintenue à l'instant présent, pour
     * qu'un délai nul ne ressorte pas les créneaux déjà passés du jour même.
     *
     * @param  array{
     *     availabilities: Collection<int, Availability>,
     *     overridesByDate: Collection<string, Collection<int, DateOverride>>,
     *     bookedByDate: Collection<string, Collection<int, Appointment>>
     * }  $context
     * @return Collection<int, array{
     *     start: CarbonImmutable,
     *     end: CarbonImmutable,
     *     label: string,
     * }>
     */
    private function slotsForDateInContext(AppointmentService $service, CarbonImmutable $date, array $context): Collection
    {
        $date = $date->startOfDay();
        $now = CarbonImmutable::now();

        if ($date->greaterThan($now->addDays($service->max_advance_days)->endOfDay())) {
            return collect();
        }

        $overrides = $context['overridesByDate']->get($date->toDateString(), collect());
        if ($overrides->contains(fn (DateOverride $o) => $o->isFullDay())) {
            return collect();
        }

        $minBookable = $now->addHours($service->min_notice_hours)->startOfDay()->max($now);

        $booked = $context['bookedByDate']->get($date->toDateString(), collect())
            ->map(fn (Appointment $appointment) => [
                'start' => CarbonImmutable::parse($appointment->starts_at),
                'end' => CarbonImmutable::parse($appointment->ends_at),
            ]);

        return $context['availabilities']
            ->where('day_of_week', $date->dayOfWeek)
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
            ->unique(fn (array $slot) => $slot['start']->getTimestamp())
            ->sortBy(fn (array $slot) => $slot['start']->getTimestamp())
            ->values()
            ->map(fn (array $slot) => [
                'start' => $slot['start'],
                'end' => $slot['end'],
                'label' => $slot['start']->format('H:i'),
            ]);
    }

    /**
     * Indique si une plage horaire tombe dans les horaires d'ouverture de la
     * prestation et hors de toute fermeture exceptionnelle. Ne dit rien des
     * rendez-vous déjà posés : sert à avertir l'admin qui saisit un
     * rendez-vous hors créneaux, pas à l'en empêcher.
     */
    public function isWithinOpeningHours(
        AppointmentService $service,
        CarbonImmutable $start,
        CarbonImmutable $end,
    ): bool {
        $date = $start->startOfDay();

        $overrides = DateOverride::query()
            ->whereDate('date', $date->toDateString())
            ->get();

        foreach ($overrides as $override) {
            if ($override->isFullDay()) {
                return false;
            }

            $blockStart = $this->applyTime($date, $override->start_time);
            $blockEnd = $this->applyTime($date, $override->end_time);

            if ($start->lessThan($blockEnd) && $end->greaterThan($blockStart)) {
                return false;
            }
        }

        return Availability::query()
            ->where('is_active', true)
            ->where('day_of_week', $start->dayOfWeek)
            ->where(function ($query) use ($service) {
                $query->whereNull('appointment_service_id')
                    ->orWhere('appointment_service_id', $service->id);
            })
            ->get()
            ->contains(function (Availability $availability) use ($date, $start, $end) {
                $windowStart = $this->applyTime($date, $availability->start_time);
                $windowEnd = $this->applyTime($date, $availability->end_time);

                return $start->greaterThanOrEqualTo($windowStart)
                    && $end->lessThanOrEqualTo($windowEnd);
            });
    }

    /**
     * Vérifie côté serveur qu'un début de créneau précis est réellement
     * réservable.
     */
    public function isSlotBookable(AppointmentService $service, CarbonImmutable $start): bool
    {
        return $this->slotsForDate($service, $start)
            ->contains(fn (array $slot) => $slot['start']->equalTo($start));
    }

    /**
     * Indique si un autre rendez-vous bloquant chevauche la plage horaire de
     * celui-ci. Sert à détecter un créneau pris pendant le tunnel de paiement.
     */
    public function hasConflictingAppointment(Appointment $appointment): bool
    {
        return $this->hasOverlap(
            $appointment->appointment_service_id,
            $appointment->starts_at,
            $appointment->ends_at,
            $appointment->id,
        );
    }

    /**
     * Indique si un autre rendez-vous bloquant chevauche la fenêtre donnée pour
     * cette prestation. Utilisé par le formulaire admin pour empêcher la
     * création/modification d'un rendez-vous en double-réservation.
     */
    public function hasOverlap(int $serviceId, CarbonInterface $start, CarbonInterface $end, ?int $excludingAppointmentId = null): bool
    {
        $query = $this->overlapQuery($serviceId, $start, $end);

        if ($excludingAppointmentId !== null) {
            $query->where('id', '!=', $excludingAppointmentId);
        }

        return $query->exists();
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
     * Déplace de façon atomique un rendez-vous existant vers un nouveau
     * créneau.
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
     * Découpe une fenêtre de disponibilité en créneaux consécutifs selon la
     * durée du service.
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
