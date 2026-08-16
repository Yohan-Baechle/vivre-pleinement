<?php

use App\Models\AppointmentService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('displays the session price and duration in the booking hero', function () {
    AppointmentService::factory()->create([
        'price_cents' => 5000,
        'duration_minutes' => 60,
    ]);

    $html = $this->get(route('booking.index'))->assertOk()->getContent();

    expect($html)->toContain('séance de 60')
        ->and(substr_count($html, '50,00'))->toBeGreaterThanOrEqual(2);
});
