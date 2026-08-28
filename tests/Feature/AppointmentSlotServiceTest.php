<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Availability;
use App\Models\DateOverride;
use App\Services\AppointmentSlotService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

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

    Availability::factory()->create([
        'day_of_week' => $dayOfWeek,
        'start_time' => $start,
        'end_time' => $end,
    ]);

    return $service;
}

it('generates slots stepped by the service duration', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $day);

    expect($slots)->toHaveCount(6)
        ->and($slots->first()['label'])->toBe('09:00')
        ->and($slots->last()['label'])->toBe('11:30');
});

it('excludes the days inside the minimum-notice window', function () {
    $today = CarbonImmutable::now();
    $service = serviceWithAvailability($today->dayOfWeek, '00:00', '23:30', ['min_notice_hours' => 24]);

    expect(app(AppointmentSlotService::class)->slotsForDate($service, $today->startOfDay()))->toBeEmpty();
});

it('keeps the late slots of an open day whatever the hour of the day', function () {
    $monday = CarbonImmutable::now()->startOfWeek()->addWeek()->setTime(17, 3);
    $this->travelTo($monday);

    $service = serviceWithAvailability(3, '17:00', '19:00', ['min_notice_hours' => 48]);
    $wednesday = $monday->startOfDay()->addDays(2);

    $labels = app(AppointmentSlotService::class)->slotsForDate($service, $wednesday)->pluck('label');

    expect($labels)->toContain('17:00')
        ->and($labels)->toContain('18:00');
});

it('never offers a slot already gone when no notice is required', function () {
    $this->travelTo(CarbonImmutable::now()->startOfDay()->addHours(12));

    $today = CarbonImmutable::now();
    $service = serviceWithAvailability($today->dayOfWeek, '09:00', '18:00', ['min_notice_hours' => 0]);

    $labels = app(AppointmentSlotService::class)->slotsForDate($service, $today->startOfDay())->pluck('label');

    expect($labels)->not->toContain('09:00')
        ->and($labels)->toContain('12:00');
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

    DateOverride::factory()->closed()->create(['date' => $day->toDateString()]);

    expect(app(AppointmentSlotService::class)->slotsForDate($service, $day))->toBeEmpty();
});

it('blocks only the overlapping range of a partial override', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    DateOverride::factory()->partial('09:00', '10:00')->create(['date' => $day->toDateString()]);

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

it('computes a full month of availability in three queries', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek);

    $this->expectsDatabaseQueryCount(3);

    app(AppointmentSlotService::class)->availableDaysForMonth($service, $day->year, $day->month);
});

it('finds the next available slots in three queries', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek);

    $this->expectsDatabaseQueryCount(3);

    app(AppointmentSlotService::class)->nextAvailableSlots($service);
});

it('never offers the same start time twice when windows overlap', function () {
    $day = nextWeekday(3);
    $service = serviceWithAvailability($day->dayOfWeek, '09:00', '12:00');

    Availability::factory()->create([
        'day_of_week' => $day->dayOfWeek,
        'start_time' => '09:00',
        'end_time' => '11:00',
    ]);

    $slots = app(AppointmentSlotService::class)->slotsForDate($service, $day);
    $labels = $slots->pluck('label')->all();

    expect($labels)->toBe(array_values(array_unique($labels)))
        ->and($slots)->toHaveCount(6);
});
