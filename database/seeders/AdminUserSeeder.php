<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@vivre-pleinement.local')],
            [
                'name' => env('ADMIN_NAME', 'Laura B.'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ],
        );
    }
}
