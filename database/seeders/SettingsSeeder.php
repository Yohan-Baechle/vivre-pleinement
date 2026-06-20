<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\Settings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $defaults = [
            'meet_url' => '',
            'notify_email' => config('mail.contact_to'),
            'reminder_24h_enabled' => '1',
            'reminder_1h_enabled' => '1',
            'followup_enabled' => '1',
            'timezone' => 'Europe/Paris',
            'contact_email' => config('mail.contact_to'),
            'contact_phone' => '',
            'social_instagram' => '',
            'social_facebook' => '',
            'social_youtube' => '',
            'social_tiktok' => '',
            'comments_enabled' => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Settings::flush();
    }
}
