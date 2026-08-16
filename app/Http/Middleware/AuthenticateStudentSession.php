<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Équivalent d'`AuthenticateSession` pour le guard « student ».
 *
 * Le middleware du framework est câblé sur le guard par défaut (`web`) et ne
 * sait pas cibler un autre guard ; l'espace élève resterait donc sans
 * garde-fou.
 *
 * Le principe : la session mémorise l'empreinte du mot de passe au moment de la
 * connexion. Dès que le mot de passe change — depuis « Mon compte » ou par
 * réinitialisation — toutes les autres sessions portent une empreinte périmée
 * et sont déconnectées à leur requête suivante. Sans ça, une session volée
 * survit au changement de mot de passe, c'est-à-dire au geste exact que fait la
 * victime quand elle soupçonne une compromission.
 */
class AuthenticateStudentSession
{
    private const SESSION_KEY = 'password_hash_student';

    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user('student');

        if (! $request->hasSession() || $student === null || blank($student->getAuthPassword())) {
            return $next($request);
        }

        if (! $request->session()->has(self::SESSION_KEY)) {
            $request->session()->put(self::SESSION_KEY, $student->getAuthPassword());
        }

        if (! hash_equals((string) $request->session()->get(self::SESSION_KEY), $student->getAuthPassword())) {
            $this->logout($request);
        }

        return tap($next($request), function () use ($request): void {
            $student = $request->user('student');

            if ($student !== null) {
                $request->session()->put(self::SESSION_KEY, $student->getAuthPassword());
            }
        });
    }

    /**
     * @throws AuthenticationException
     */
    private function logout(Request $request): void
    {
        Auth::guard('student')->logoutCurrentDevice();

        $request->session()->flush();

        throw new AuthenticationException('Unauthenticated.', ['student'], route('student.login'));
    }
}
