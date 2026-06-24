<?php

namespace App\Http\Controllers;

use App\Services\YoutubeCaptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

    /**
     * Redirige vers l'écran de consentement Google.
     */
    public function redirect(Request $request)
    {
        $clientId = config('services.youtube.oauth_client_id');

        abort_unless($clientId, 404);

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => url(self::REDIRECT_PATH),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * Reçoit le code de Google et affiche le refresh token à copier dans .env.
     */
    public function callback(Request $request)
    {
        abort_unless(config('services.youtube.oauth_client_id'), 404);

        if ($error = $request->query('error')) {
            return Response::make('Autorisation refusée : '.e($error), 400)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $code = (string) $request->query('code');

        if ($code === '') {
            return Response::make('Code d\'autorisation manquant.', 400)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        try {
            $tokens = YoutubeCaptions::fromConfig()->exchangeAuthorizationCode($code, url(self::REDIRECT_PATH));
        } catch (Throwable $e) {
            return Response::make('Échec de l\'échange : '.e($e->getMessage()), 500)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $refreshToken = $tokens['refresh_token'] ?? null;

        if (! $refreshToken) {
            return Response::make(
                'Google n\'a pas renvoyé de refresh token. Révoquez l\'accès de l\'app dans votre compte Google puis recommencez.',
                400,
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }

        $html = <<<HTML
        <!doctype html><html lang="fr"><head><meta charset="utf-8">
        <title>YouTube OAuth — Succès</title>
        <style>body{font-family:system-ui,sans-serif;max-width:640px;margin:4rem auto;padding:0 1rem;line-height:1.6;color:#1f2937}
        code{display:block;background:#f3f4f6;padding:1rem;border-radius:.5rem;word-break:break-all;margin:1rem 0;font-size:.9rem}
        .ok{color:#047857}</style></head><body>
        <h1 class="ok">✅ Autorisation réussie</h1>
        <p>Copiez cette ligne dans votre fichier <strong>.env</strong> :</p>
        <code>YOUTUBE_OAUTH_REFRESH_TOKEN={$refreshToken}</code>
        <p>Puis lancez&nbsp;:</p>
        <code>vendor/bin/sail artisan youtube:fetch-transcripts --limit=3</code>
        <p>Vous pouvez fermer cet onglet.</p>
        </body></html>
        HTML;

        return Response::make($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
