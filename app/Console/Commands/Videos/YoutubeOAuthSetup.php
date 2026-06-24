<?php

namespace App\Console\Commands\Videos;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Affiche le lien d'autorisation OAuth à ouvrir dans le navigateur. Le flux
 * « Application Web » (route /youtube/oauth/redirect) capte ensuite le code et
 * affiche le refresh token à coller dans .env.
 */
#[Signature('youtube:oauth-setup')]
#[Description('Affiche le lien d\'autorisation OAuth YouTube pour récupérer le refresh token (sous-titres).')]
class YoutubeOAuthSetup extends Command
{
    public function handle(): int
    {
        $clientId = config('services.youtube.oauth_client_id');
        $clientSecret = config('services.youtube.oauth_client_secret');

        if (! $clientId || ! $clientSecret) {
            $this->error('Renseignez d\'abord YOUTUBE_OAUTH_CLIENT_ID et YOUTUBE_OAUTH_CLIENT_SECRET dans .env.');

            return self::FAILURE;
        }

        $url = route('youtube.oauth.redirect');

        $this->info('Ouvrez ce lien dans le navigateur connecté au compte Google propriétaire de la chaîne :');
        $this->newLine();
        $this->line($url);
        $this->newLine();
        $this->info('Autorisez l\'accès : la page de retour affichera la ligne YOUTUBE_OAUTH_REFRESH_TOKEN à coller dans .env.');

        return self::SUCCESS;
    }
}
