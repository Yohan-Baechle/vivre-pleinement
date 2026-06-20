<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Limitation d'envois pour les formulaires publics (contact, commentaires).
 */
class SubmissionThrottle
{
    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 600;

    /**
     * Indique si la limite est atteinte pour cette clé, sans la consommer.
     */
    public static function exceeded(string $key): bool
    {
        return RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS);
    }

    /**
     * Nombre de secondes avant un nouvel essai possible.
     */
    public static function availableIn(string $key): int
    {
        return RateLimiter::availableIn($key);
    }

    /**
     * Enregistre une tentative pour cette clé.
     */
    public static function hit(string $key): void
    {
        RateLimiter::hit($key, self::DECAY_SECONDS);
    }
}
