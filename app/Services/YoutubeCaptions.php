<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Récupère les sous-titres d'une chaîne YouTube via l'API officielle.
 *
 * Le téléchargement des sous-titres (captions.download) exige un accès OAuth
 * en tant que propriétaire de la chaîne : une simple clé API ne suffit pas.
 * Ce service gère l'échange du refresh token contre un access token, puis le
 * listing et le téléchargement des pistes.
 */
class YoutubeCaptions
{
    private const API_BASE = 'https://www.googleapis.com/youtube/v3';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private ?string $accessToken = null;

    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $clientId,
        private readonly ?string $clientSecret,
        private readonly ?string $refreshToken,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            apiKey: config('services.youtube.api_key'),
            clientId: config('services.youtube.oauth_client_id'),
            clientSecret: config('services.youtube.oauth_client_secret'),
            refreshToken: config('services.youtube.oauth_refresh_token'),
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->clientId)
            && filled($this->clientSecret)
            && filled($this->refreshToken);
    }

    /**
     * Échange un code d'autorisation contre les jetons OAuth (setup initial).
     *
     * @return array{access_token: string, refresh_token?: string, expires_in: int}
     */
    public function exchangeAuthorizationCode(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Échec de l\'échange du code OAuth : '.$response->body());
        }

        return $response->json();
    }

    /**
     * Liste les pistes de sous-titres d'une vidéo.
     *
     * @return list<array{id: string, language: string, trackKind: string}>
     */
    public function listTracks(string $videoId): array
    {
        $response = $this->client()->get(self::API_BASE.'/captions', [
            'part' => 'snippet',
            'videoId' => $videoId,
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Échec du listing des sous-titres ({$videoId}) : ".$response->body());
        }

        return collect($response->json('items') ?? [])
            ->map(fn (array $item) => [
                'id' => $item['id'],
                'language' => $item['snippet']['language'] ?? '',
                'trackKind' => $item['snippet']['trackKind'] ?? '',
            ])
            ->all();
    }

    /**
     * Sélectionne la meilleure piste pour une langue : sous-titres validés
     * (standard) en priorité, sinon la transcription automatique (asr).
     *
     * @param  list<array{id: string, language: string, trackKind: string}>  $tracks
     */
    public function pickBestTrackId(array $tracks, string $language = 'fr'): ?string
    {
        $forLang = array_filter($tracks, fn ($t) => str_starts_with($t['language'], $language));

        foreach (['standard', 'asr'] as $kind) {
            foreach ($forLang as $track) {
                if ($track['trackKind'] === $kind) {
                    return $track['id'];
                }
            }
        }

        return null;
    }

    /**
     * Télécharge le contenu brut d'une piste de sous-titres (format SRT).
     */
    public function downloadTrack(string $captionId): string
    {
        $response = $this->client()->get(self::API_BASE."/captions/{$captionId}", [
            'tfmt' => 'srt',
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Échec du téléchargement du sous-titre ({$captionId}) : ".$response->body());
        }

        return $response->body();
    }

    /**
     * Récupère un access token frais à partir du refresh token.
     */
    private function accessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'OAuth YouTube non configuré. Renseignez YOUTUBE_OAUTH_CLIENT_ID, '
                .'YOUTUBE_OAUTH_CLIENT_SECRET et YOUTUBE_OAUTH_REFRESH_TOKEN dans .env '
                .'(voir la commande youtube:oauth-setup).'
            );
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Échec du rafraîchissement du token OAuth : '.$response->body());
        }

        return $this->accessToken = $response->json('access_token');
    }

    private function client(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->timeout(30)
            ->retry(2, 500)
            ->acceptJson();
    }
}
