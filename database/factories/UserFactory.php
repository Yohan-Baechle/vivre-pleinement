<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Mot de passe courant utilisé par la factory.
     */
    protected static ?string $password;

    /**
     * La double authentification est configurée par défaut : le panneau
     * d'administration l'exige, un compte qui ne l'a pas est redirigé vers sa
     * page de configuration. Le défaut reflète donc l'état normal en
     * production.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'app_authentication_secret' => Str::random(32),
        ];
    }

    /**
     * Indique que l'adresse e-mail du modèle doit être non vérifiée.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Compte qui n'a pas encore activé la double authentification.
     */
    public function withoutMultiFactorAuthentication(): static
    {
        return $this->state(fn (array $attributes) => [
            'app_authentication_secret' => null,
        ]);
    }
}
