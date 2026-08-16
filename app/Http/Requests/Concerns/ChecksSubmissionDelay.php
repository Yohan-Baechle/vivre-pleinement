<?php

namespace App\Http\Requests\Concerns;

use App\Support\SubmissionStamp;
use Illuminate\Validation\ValidationException;

/**
 * Honeypot temporel : rejette les formulaires soumis trop vite pour être
 * humains.
 *
 * Le champ `ts` porte l'horodatage d'affichage du formulaire, chiffré par
 * l'application. En clair, il était recopiable par n'importe quel script — il
 * suffisait de poster `ts = time() - 10` — et ne filtrait donc que les robots
 * qui rejouaient le formulaire sans le relire.
 */
trait ChecksSubmissionDelay
{
    private const MIN_DELAY_SECONDS = 3;

    /**
     * Vérifie le délai minimum après validation des règles.
     */
    public function passedValidation(): void
    {
        $issuedAt = SubmissionStamp::read($this->input('ts'));

        if ($issuedAt === null || (time() - $issuedAt) < self::MIN_DELAY_SECONDS) {
            throw ValidationException::withMessages([
                'ts' => 'Envoi trop rapide, veuillez réessayer.',
            ]);
        }
    }
}
