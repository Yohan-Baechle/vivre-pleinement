<?php

namespace App\Http\Controllers;

use App\Services\YoutubeCaptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

/**
 * Flux OAuth « Application Web » pour autoriser une seule fois l'accès aux
 * sous-titres de la chaîne. Volontairement minimal et non lié à une session :
 * il sert uniquement à récupérer le refresh token lors de l'installation.
 */
class YoutubeOAuthController extends Controller
{
    public const REDIRECT_PATH = '/youtube/oauth/callback';

    private const SCOPE = 'https://www.googleapis.com/auth/youtube.force-ssl';

    private const STATE_SESSION_KEY = 'youtube_oauth_state';

    /**
     * Redirige vers l'écran de consentement Google. Le paramètre state est
     * généré et stocké en session, puis revérifié au retour pour empêcher
     * qu'un tiers ne déclenche le callback avec son propre code d'autorisation
     * (CSRF sur le flux OAuth).
     */
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.youtube.oauth_client_id');

        abort_unless($clientId, 404);

        $state = Str::random(40);
        $request->session()->put(self::STATE_SESSION_KEY, $state);

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => url(self::REDIRECT_PATH),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * Reçoit le code de Google et affiche le refresh token à copier dans .env.
     */
    public function callback(Request $request): Response
    {
        abort_unless(config('services.youtube.oauth_client_id'), 404);

        $expectedState = $request->session()->pull(self::STATE_SESSION_KEY);

        if (! is_string($expectedState) || ! hash_equals($expectedState, (string) $request->query('state'))) {
            return $this->failure('État OAuth invalide ou expiré. Recommencez le flux depuis /youtube/oauth/redirect.');
        }

        if ($error = $request->query('error')) {
            return $this->failure('Autorisation refusée : '.$error);
        }

        $code = (string) $request->query('code');

        if ($code === '') {
            return $this->failure('Code d\'autorisation manquant.');
        }

        try {
            $tokens = YoutubeCaptions::fromConfig()->exchangeAuthorizationCode($code, url(self::REDIRECT_PATH));
        } catch (Throwable $e) {
            return $this->failure('Échec de l\'échange : '.$e->getMessage(), 500);
        }

        $refreshToken = $tokens['refresh_token'] ?? null;

        if (! $refreshToken) {
            return $this->failure(
                'Google n\'a pas renvoyé de refresh token. Révoquez l\'accès de l\'app dans votre compte Google puis recommencez.',
            );
        }

        return response()->view('youtube.oauth.success', ['refreshToken' => $refreshToken]);
    }

    /**
     * Rend la page d'échec du flux OAuth avec le statut HTTP correspondant.
     */
    private function failure(string $message, int $status = 400): Response
    {
        return response()->view('youtube.oauth.error', ['message' => $message], $status);
    }
}
