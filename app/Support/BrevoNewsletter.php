<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Inscription à la liste « Vidéo gratuite » via le double opt-in Brevo.
 *
 * Brevo envoie l'email de confirmation, gère le consentement RGPD puis
 * déclenche l'automation qui délivre la vidéo une fois l'inscription validée.
 */
class BrevoNewsletter
{
    private const ENDPOINT = 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation';

    /**
     * Crée un contact en double opt-in et l'associe à la liste vidéo.
     *
     * @throws RuntimeException si la configuration est absente ou l'appel échoue
     */
    public function subscribeToVideoList(string $email, string $firstName, string $redirectionUrl): void
    {
        $apiKey = config('services.brevo.key');

        if (empty($apiKey)) {
            throw new RuntimeException('La clé API Brevo est absente.');
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
            ])->post(self::ENDPOINT, [
                'email' => $email,
                'attributes' => ['PRENOM' => $firstName],
                'includeListIds' => [config('services.brevo.video_list_id')],
                'templateId' => config('services.brevo.doi_template_id'),
                'redirectionUrl' => $redirectionUrl,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Brevo est injoignable.', previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException("Brevo a refusé l'inscription (HTTP {$response->status()}).");
        }
    }
}
