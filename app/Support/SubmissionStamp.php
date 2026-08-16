<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Horodatage d'affichage d'un formulaire public, chiffré par l'application.
 *
 * Sert de garde-fou anti-robot avec ChecksSubmissionDelay : la valeur voyage
 * dans un champ caché, donc elle doit être infalsifiable côté client. Le
 * chiffrement authentifié de Laravel (AES-256-CBC + HMAC) suffit ici, la
 * donnée n'étant ni secrète ni sensible — seule son intégrité compte.
 */
class SubmissionStamp
{
    /**
     * Valeur à placer dans le champ caché du formulaire.
     */
    public static function issue(): string
    {
        return Crypt::encryptString((string) time());
    }

    /**
     * Horodatage d'origine, ou null si la valeur est absente, illisible ou
     * n'a pas été émise par cette application.
     */
    public static function read(mixed $value): ?int
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }

        return ctype_digit($decrypted) ? (int) $decrypted : null;
    }
}
