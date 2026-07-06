<?php

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Filament\Admin\Resources\Appointments\Pages\CreateAppointment;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel('admin');
});

it('renders the appointment create page', function () {
    $this->get(route('filament.admin.resources.appointments.create'))->assertOk();
});

it('auto-computes the end time from the service duration', function () {
    $service = AppointmentService::factory()->create(['duration_minutes' => 45]);
    $start = CarbonImmutable::now()->addDays(7)->setTime(10, 0);

    Livewire::test(CreateAppointment::class)
        ->set('data.appointment_service_id', $service->id)
        ->set('data.starts_at', $start->format('Y-m-d H:i:s'))
        ->assertSet('data.ends_at', $start->addMinutes(45)->format('Y-m-d H:i:s'));
});

it('defaults the channel to video', function () {
    Livewire::test(CreateAppointment::class)
        ->assertSet('data.channel', AppointmentChannel::Video->value);
});

it('submits the create form and persists an appointment with an auto-generated reference and token', function () {
    $service = AppointmentService::factory()->create(['duration_minutes' => 45]);
    $start = CarbonImmutable::now()->addDays(7)->setTime(10, 0);

    Livewire::test(CreateAppointment::class)
        ->fillForm([
            'appointment_service_id' => $service->id,
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'ends_at' => $start->addMinutes(45)->format('Y-m-d H:i:s'),
            'customer_first_name' => 'Camille',
            'customer_email' => 'camille@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $appointment = Appointment::query()->firstOrFail();
    expect($appointment->reference)->not->toBeNull()
        ->and($appointment->token)->not->toBeNull();
});

it('rejects an overlapping appointment on the admin form', function () {
    $service = AppointmentService::factory()->create(['duration_minutes' => 60]);
    $start = CarbonImmutable::now()->addDays(7)->setTime(10, 0);

    Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'status' => AppointmentStatus::Confirmed,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(60),
    ]);

    Livewire::test(CreateAppointment::class)
        ->fillForm([
            'appointment_service_id' => $service->id,
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'ends_at' => $start->addMinutes(60)->format('Y-m-d H:i:s'),
            'customer_first_name' => 'Camille',
            'customer_email' => 'camille@example.com',
        ])
        ->call('create')
        ->assertHasFormErrors(['ends_at']);
});
