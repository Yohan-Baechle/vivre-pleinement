<?php

use App\Enums\AppointmentStatus;
use App\Filament\Admin\Resources\Appointments\Pages\ListAppointments;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentNoShow;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function adminAppointment(?CarbonImmutable $start = null): Appointment
{
    $start ??= CarbonImmutable::now()->addDays(4)->setTime(10, 0);
    $service = AppointmentService::factory()->create(['duration_minutes' => 30]);

    return Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'token' => Appointment::generateToken(),
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ]);
}

it('notifies the client when an admin cancels', function () {
    $appointment = adminAppointment();

    Livewire::test(ListAppointments::class)
        ->callAction(TestAction::make('cancel')->table($appointment))
        ->assertHasNoActionErrors();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);
    Mail::assertQueued(AppointmentCancelled::class, 1);
});

it('moves an appointment and notifies the client when an admin reschedules', function () {
    $appointment = adminAppointment();
    $newStart = CarbonImmutable::now()->addDays(6)->setTime(15, 0);

    Livewire::test(ListAppointments::class)
        ->callAction(TestAction::make('reschedule')->table($appointment), data: [
            'starts_at' => $newStart->format('Y-m-d H:i:s'),
        ])
        ->assertHasNoActionErrors();

    expect($appointment->fresh()->starts_at->equalTo($newStart))->toBeTrue();
    Mail::assertQueued(AppointmentRescheduled::class, 1);
});

it('marks a past confirmed appointment as no-show and emails the client', function () {
    $past = CarbonImmutable::now()->subDay()->setTime(10, 0);
    $appointment = adminAppointment($past);

    // Les RDV passés sont masqués par le filtre « À venir » actif par défaut.
    Livewire::test(ListAppointments::class)
        ->removeTableFilter('upcoming')
        ->callAction(TestAction::make('noShow')->table($appointment))
        ->assertHasNoActionErrors();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::NoShow);
    Mail::assertQueued(AppointmentNoShow::class, 1);
});

it('hides the no-show action for a future appointment', function () {
    $appointment = adminAppointment(CarbonImmutable::now()->addDays(2)->setTime(10, 0));

    Livewire::test(ListAppointments::class)
        ->assertActionHidden(TestAction::make('noShow')->table($appointment));
});

it('refuses to reschedule onto an occupied slot', function () {
    $appointment = adminAppointment();
    $occupiedStart = CarbonImmutable::now()->addDays(6)->setTime(15, 0);

    Appointment::factory()->create([
        'appointment_service_id' => $appointment->appointment_service_id,
        'starts_at' => $occupiedStart,
        'ends_at' => $occupiedStart->addMinutes(30),
        'status' => AppointmentStatus::Confirmed,
    ]);

    Livewire::test(ListAppointments::class)
        ->callAction(TestAction::make('reschedule')->table($appointment), data: [
            'starts_at' => $occupiedStart->format('Y-m-d H:i:s'),
        ]);

    expect($appointment->fresh()->starts_at->equalTo($occupiedStart))->toBeFalse();
    Mail::assertNotQueued(AppointmentRescheduled::class);
});
