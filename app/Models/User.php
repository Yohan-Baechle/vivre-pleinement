<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Les deux traits déclarent eux-mêmes le chiffrement et le masquage des
     * colonnes `app_authentication_*`, inutile de les répéter dans casts().
     */
    use InteractsWithAppAuthentication;

    use InteractsWithAppAuthenticationRecovery;

    /**
     * Autorise l'accès au panneau d'administration.
     *
     * Le site n'a pas de comptes « visiteurs » : les réservations se font sans
     * compte, donc tout utilisateur enregistré est un administrateur.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Les colonnes de double authentification sont déclarées ici pour être
     * présentes dès la création d'un compte : sans ça, un modèle tout juste
     * créé ne les porte pas et `Model::shouldBeStrict()` lève une
     * MissingAttributeException quand Filament vérifie si la MFA est active.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'app_authentication_secret' => null,
        'app_authentication_recovery_codes' => null,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
