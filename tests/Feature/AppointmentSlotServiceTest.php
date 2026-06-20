<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Models\DateOverride;
use App\Services\AppointmentSlotService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Returns the next occurrence of a given weekday (Carbon dayOfWeek, 0=Sun),
 * at least $minDays from now, so slots clear the min-notice window.
 */
function nextWeekday(int $dayOfWeek, int $minDays = 2): CarbonImmutable
{
    $date = CarbonImmutable::now()->addDays($minDays)->startOfDay();
    while ($date->dayOfWeek !== $dayOfWeek) {
        $date = $date->addDay();
    }

    return $date;
}

function serviceWithAvailability(int $dayOfWeek, string $start = '09:00', string $end = '12:00', array $attributes = []): AppointmentService
{
    $service = AppointmentService::factory()->create(array_merge([
        'duration_minutes' => 30,
        'buffer_minutes' => 0,
        'min_notice_hours' => 12,
        'max_advance_days' => 60,
    ], $attributes));

    Availability::create([
        'appointment_service_id' => null,
        'day_of_week' => $dayOfWeek,
        'start_time' => $start,
        'end_time' => $end,
        'is_active' => true,
    ]);

    return $service;
}

it('generates slots stepped by the service duration', function () {
    $day = nextWeekday(3); // mercredi
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $day);

    // 3h / 30 min = 6 créneaux : 09:00 … 11:30
    expect($slots)->toHaveCount(6)
        ->and($slots->first()['label'])->toBe('09:00')
        ->and($slots->last()['label'])->toBe('11:30');
});

it('excludes slots inside the minimum-notice window', function () {
    // Disponibilité aujourd'hui, mais min_notice de 12h => créneaux du matin écartés.
    $today = CarbonImmutable::now();
    $service = serviceWithAvailability($today->dayOfWeek, '00:00', '23:30', ['min_notice_hours' => 12]);

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $today->startOfDay());

    $minBookable = $today->addHours(12);
    expect($slots->every(fn ($slot) => $slot['start']->greaterThanOrEqualTo($minBookable)))->toBeTrue();
});

it('excludes dates beyond the booking horizon', function () {
    $service = serviceWithAvailability(CarbonImmutable::now()->dayOfWeek, '09:00', '12:00', ['max_advance_days' => 7]);

    $farDate = CarbonImmutable::now()->addDays(30)->startOfDay();

    expect(app(AppointmentSlotService::class)->slotsForDate($service, $farDate))->toBeEmpty();
});

it('excludes slots overlapping an existing appointment', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    $start = $day->setTime(9, 0);
    Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $day);

    expect($slots->pluck('label'))->not->toContain('09:00')
        ->and($slots)->toHaveCount(5);
});

it('keeps slots when the overlapping appointment is cancelled', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    $start = $day->setTime(9, 0);
    Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(30),
        'status' => AppointmentStatus::Cancelled,
    ]);

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $day);

    expect($slots->pluck('label'))->toContain('09:00');
});

it('blocks the whole day with a full-day override', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    DateOverride::create(['date' => $day->toDateString()]);

    expect(app(AppointmentSlotService::class)->slotsForDate($service, $day))->toBeEmpty();
});

it('blocks only the overlapping range of a partial override', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    DateOverride::create([
        'date' => $day->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $day);

    expect($slots->pluck('label'))->not->toContain('09:00')
        ->and($slots->pluck('label'))->not->toContain('09:30')
        ->and($slots->pluck('label'))->toContain('10:00');
});

it('confirms a specific slot is bookable', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    $start = $day->setTime(10, 0);

    expect(app(AppointmentSlotService::class)->isSlotBookable($service, $start))->toBeTrue()
        ->and(app(AppointmentSlotService::class)->isSlotBookable($service, $day->setTime(13, 0)))->toBeFalse();
});
