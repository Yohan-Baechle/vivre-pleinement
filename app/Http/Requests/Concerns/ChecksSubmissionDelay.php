<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\ValidationException;

/**
 * Honeypot temporel : rejette les formulaires soumis trop vite pour être humains.
 * Le champ `ts` porte l'horodatage d'affichage du formulaire.
 */
trait ChecksSubmissionDelay
{
    private const MIN_DELAY_SECONDS = 3;

    /**
     * Vérifie le délai minimum après validation des règles.
     */
    public function passedValidation(): void
    {
        $elapsed = time() - (int) $this->input('ts');

        if ($elapsed < self::MIN_DELAY_SECONDS) {
            throw ValidationException::withMessages([
                'ts' => 'Envoi trop rapide, veuillez réessayer.',
            ]);
        }
    }
}
