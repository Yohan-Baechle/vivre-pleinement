<?php

namespace Database\Seeders;

use App\Models\AppointmentService;
use App\Models\Availability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crée la prestation par défaut et ses disponibilités hebdomadaires globales
     * (day_of_week : 1 = lundi, 2 = mardi, 4 = jeudi), matin et après-midi.
     */
    public function run(): void
    {
        AppointmentService::query()->updateOrCreate(
            ['slug' => 'seance-individuelle'],
            [
                'name' => 'Accompagnement ACT',
                'description' => 'Une séance d\'accompagnement individuelle d\'une heure, par téléphone ou en visioconférence, basée sur l\'ACT, pour avancer concrètement sur vos objectifs.',
                'duration_minutes' => 60,
                'price_cents' => 5000,
                'currency' => 'EUR',
                'buffer_minutes' => 15,
                'min_notice_hours' => 24,
                'max_advance_days' => 60,
                'requires_confirmation' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        $weeklySlots = [
            ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '12:00'],
            ['day_of_week' => 1, 'start_time' => '14:00', 'end_time' => '18:00'],
            ['day_of_week' => 2, 'start_time' => '09:00', 'end_time' => '12:00'],
            ['day_of_week' => 4, 'start_time' => '14:00', 'end_time' => '18:00'],
        ];

        foreach ($weeklySlots as $slot) {
            Availability::query()->updateOrCreate(
                [
                    'appointment_service_id' => null,
                    'day_of_week' => $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                ],
                [
                    'end_time' => $slot['end_time'],
                    'is_active' => true,
                ],
            );
        }
    }
}
