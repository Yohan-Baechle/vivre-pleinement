<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crée ou met à jour le compte d'accès au panneau Filament.
     *
     * Le mot de passe doit venir de l'environnement : comme ce seeder utilise
     * updateOrCreate, un mot de passe par défaut réinitialiserait le compte
     * admin sur une valeur publiquement connue à chaque exécution en
     * production.
     */
    public function run(): void
    {
        $password = config('admin.password');

        if (blank($password)) {
            throw new RuntimeException(
                'ADMIN_PASSWORD est absent : impossible de créer le compte administrateur sans mot de passe explicite.'
            );
        }

        User::updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'password' => Hash::make($password),
            ],
        );
    }
}
