<?php

namespace App\Console\Commands\Videos;

use App\Services\YoutubeCaptions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

/**
 * Assistant d'autorisation OAuth (à lancer une seule fois) pour obtenir le
 * refresh token permettant de télécharger les sous-titres de la chaîne.
 */
#[Signature('youtube:oauth-setup')]
#[Description('Assistant unique : génère le refresh token OAuth YouTube pour récupérer les sous-titres.')]
class YoutubeOAuthSetup extends Command
{
    /** Flux « appareil hors-navigateur » : Google renvoie le code à coller. */
    private const REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';

    private const SCOPE = 'https://www.googleapis.com/auth/youtube.force-ssl';

    public function handle(): int
    {
        $clientId = config('services.youtube.oauth_client_id');
        $clientSecret = config('services.youtube.oauth_client_secret');

        if (! $clientId || ! $clientSecret) {
            $this->error('Renseignez d\'abord YOUTUBE_OAUTH_CLIENT_ID et YOUTUBE_OAUTH_CLIENT_SECRET dans .env.');

            return self::FAILURE;
        }

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        $this->info('1. Ouvrez cette URL dans le navigateur connecté au compte Google propriétaire de la chaîne :');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();
        $this->info('2. Autorisez l\'accès, puis copiez le code affiché par Google.');
        $this->newLine();

        $code = trim((string) $this->ask('3. Collez le code d\'autorisation ici'));

        if ($code === '') {
            $this->error('Aucun code fourni.');

            return self::FAILURE;
        }

        try {
            $tokens = YoutubeCaptions::fromConfig()->exchangeAuthorizationCode($code, self::REDIRECT_URI);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $refreshToken = $tokens['refresh_token'] ?? null;

        if (! $refreshToken) {
            $this->error('Google n\'a pas renvoyé de refresh token. Réessayez en révoquant l\'accès précédent (prompt=consent est déjà forcé).');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Autorisation réussie. Ajoutez cette ligne dans votre .env :');
        $this->newLine();
        $this->line('YOUTUBE_OAUTH_REFRESH_TOKEN='.$refreshToken);
        $this->newLine();
        $this->info('Puis lancez : vendor/bin/sail artisan youtube:fetch-transcripts');

        return self::SUCCESS;
    }
}
