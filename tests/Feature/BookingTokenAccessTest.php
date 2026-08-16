<?php

use App\Models\Appointment;
use App\Models\AppointmentService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function tokenAccessAppointment(): Appointment
{
    $service = AppointmentService::factory()->create(['is_active' => true]);

    return Appointment::factory()->create([
        'appointment_service_id' => $service->id,
        'customer_first_name' => 'Camille',
        'customer_email' => 'camille@example.com',
        'customer_phone' => '0612345678',
    ]);
}

it('serves the confirmation page from the appointment token', function () {
    $appointment = tokenAccessAppointment();

    $this->get(route('booking.confirmation', $appointment->token))
        ->assertOk()
        ->assertSee($appointment->reference);
});

it('does not expose the confirmation page from the short human reference', function () {
    $appointment = tokenAccessAppointment();

    $this->get('/reservation/confirmation/'.$appointment->reference)->assertNotFound();
});

it('does not expose the ics file from the short human reference', function () {
    $appointment = tokenAccessAppointment();

    $this->get('/reservation/confirmation/'.$appointment->reference.'/agenda.ics')->assertNotFound();
});

it('does not leak customer data to an unknown token', function () {
    tokenAccessAppointment();

    $this->get(route('booking.confirmation', Appointment::generateToken()))->assertNotFound();
});

it('issues a token long enough to resist enumeration', function () {
    expect(strlen(Appointment::generateToken()))->toBe(48);
});
