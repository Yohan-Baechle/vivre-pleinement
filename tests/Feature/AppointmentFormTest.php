<?php

use App\Enums\AppointmentChannel;
use App\Filament\Admin\Resources\Appointments\Pages\CreateAppointment;
use App\Models\AppointmentService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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
