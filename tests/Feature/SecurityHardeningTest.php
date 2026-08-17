<?php

use App\Models\Redirect;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;

uses(LazilyRefreshDatabase::class);

/**
 * H2 — le Host sert à construire des URL absolues qui sont mises en cache
 * (sitemap) ou transmises à un tiers (Brevo). Il ne doit pas être libre.
 */
it('n\'accepte que les hôtes du site et ses sous-domaines', function () {
    $patterns = app(TrustHosts::class)->hosts();

    $host = parse_url(config('app.url'), PHP_URL_HOST);

    expect($patterns)->toContain('^(.+\.)?'.preg_quote($host).'$');
});

it('accepte des hôtes supplémentaires déclarés par la configuration', function () {
    config(['security.trusted_hosts' => ['^vivre-pleinement\.fr$']]);

    expect(app(TrustHosts::class)->hosts())->toContain('^vivre-pleinement\.fr$');
});

/**
 * H3 — sans attribut Secure, le cookie de session part en clair à la première
 * requête http:// accidentelle.
 */
it('marque le cookie de session Secure en production même sans variable dédiée', function () {
    expect(sessionConfigFor('production')['secure'])->toBeTrue()
        ->and(sessionConfigFor('local')['secure'])->toBeFalse()
        ->and(sessionConfigFor('testing')['secure'])->toBeFalse();
});

it('garde le cookie de session inaccessible au JavaScript et en SameSite Lax', function () {
    expect(config('session.http_only'))->toBeTrue()
        ->and(config('session.same_site'))->toBe('lax');
});

/**
 * M5 — HandleRedirects remplace la réponse par une redirection neuve : elle
 * doit elle aussi porter les en-têtes de sécurité.
 */
it('pose les en-têtes de sécurité sur une redirection issue de la table', function () {
    Redirect::factory()->create([
        'from_path' => '/ancienne-page',
        'to_path' => '/blog',
        'status_code' => 301,
    ]);

    $response = $this->get('/ancienne-page');

    $response->assertRedirect(url('/blog'))
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($response->headers->get('Content-Security-Policy'))->toContain("default-src 'self'");
});

/**
 * M6 — une requête SQL par 404 permettait de charger la base en martelant des
 * URL inexistantes.
 */
it('ne requête pas la table des redirections à chaque 404', function () {
    Redirect::factory()->create(['from_path' => '/connue', 'to_path' => '/blog']);

    $this->get('/inexistante-un')->assertNotFound();

    DB::enableQueryLog();
    $this->get('/inexistante-deux')->assertNotFound();
    $queries = collect(DB::getQueryLog())->filter(
        fn (array $query): bool => str_contains($query['query'], 'redirects')
    );
    DB::disableQueryLog();

    expect($queries)->toBeEmpty();
});

it('invalide le cache des redirections dès qu\'une entrée change', function () {
    $this->get('/nouvelle-cible')->assertNotFound();

    Redirect::factory()->create([
        'from_path' => '/nouvelle-cible',
        'to_path' => '/blog',
        'status_code' => 301,
    ]);

    $this->get('/nouvelle-cible')->assertRedirect(url('/blog'));
});

it('compte les visites sans vider le cache des redirections', function () {
    $redirect = Redirect::factory()->create([
        'from_path' => '/comptee',
        'to_path' => '/blog',
        'status_code' => 301,
    ]);

    $this->get('/comptee')->assertRedirect(url('/blog'));
    $this->get('/comptee')->assertRedirect(url('/blog'));

    expect($redirect->fresh()->hit_count)->toBe(2)
        ->and($redirect->fresh()->last_hit_at)->not->toBeNull();
});

/**
 * L1 — distinguer « adresse inconnue » de « lien expiré » transforme le
 * formulaire en oracle d'énumération des comptes élèves.
 */
it('renvoie le même message que l\'adresse existe ou non sur le reset', function () {
    $student = Student::factory()->create();

    $inconnue = $this->post(route('student.password.update'), [
        'token' => 'jeton-invalide',
        'email' => 'personne@gmail.com',
        'password' => 'nouveau-mot-de-passe-solide',
        'password_confirmation' => 'nouveau-mot-de-passe-solide',
    ])->assertSessionHasErrors('email');

    $connue = $this->post(route('student.password.update'), [
        'token' => 'jeton-invalide',
        'email' => $student->email,
        'password' => 'nouveau-mot-de-passe-solide',
        'password_confirmation' => 'nouveau-mot-de-passe-solide',
    ])->assertSessionHasErrors('email');

    expect(sessionError($inconnue, 'email'))->toBe(sessionError($connue, 'email'));
});

it('accepte toujours un jeton de réinitialisation valide', function () {
    $student = Student::factory()->create();
    $token = Password::broker('students')->createToken($student);

    $this->post(route('student.password.update'), [
        'token' => $token,
        'email' => $student->email,
        'password' => 'nouveau-mot-de-passe-solide',
        'password_confirmation' => 'nouveau-mot-de-passe-solide',
    ])->assertRedirect(route('student.login'));
});

/**
 * L3 — seul le webhook dépend de la signature ; bloquer la page de
 * confirmation 3-D Secure couperait un client au milieu de son paiement.
 */
it('ne bloque que le webhook quand le secret Stripe est absent', function () {
    config(['cashier.webhook.secret' => null]);

    $this->post('stripe/webhook', [])->assertForbidden();
    $this->get('stripe/payment/pi_inexistant')->assertStatus(500);
});

/**
 * M2 — le panneau donne accès aux données personnelles des élèves : un compte
 * sans double authentification ne doit pas y entrer.
 */
it('redirige un administrateur sans double authentification vers sa configuration', function () {
    $admin = User::factory()->withoutMultiFactorAuthentication()->create();

    $this->actingAs($admin)
        ->get('/espace-pro')
        ->assertRedirect();
});

it('laisse entrer un administrateur ayant configuré sa double authentification', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)->get('/espace-pro')->assertOk();
});

it('masque et chiffre le secret de double authentification', function () {
    $admin = User::factory()->create();

    expect($admin->toArray())->not->toHaveKey('app_authentication_secret')
        ->and($admin->toArray())->not->toHaveKey('app_authentication_recovery_codes');

    $stored = DB::table('users')->where('id', $admin->id)->value('app_authentication_secret');

    expect($stored)->not->toBe($admin->getAppAuthenticationSecret());
});

function sessionError(TestResponse $response, string $key): string
{
    return $response->getSession()->get('errors')->first($key);
}

/**
 * Recharge config/session.php pour un environnement donné,
 * SESSION_SECURE_COOKIE absent, afin de vérifier la valeur de repli.
 *
 * `env()` interroge trois sources : $_ENV, putenv() puis $_SERVER. Quand le
 * fichier d'environnement porte la variable — c'est le cas de `.env.example`,
 * que la CI copie en `.env` — la vider des seuls tableaux laisse putenv() la
 * servir, et le repli n'est jamais atteint. Il faut donc neutraliser les trois.
 *
 * @return array<string, mixed>
 */
function sessionConfigFor(string $environment): array
{
    $previousEnv = $_SERVER['APP_ENV'] ?? null;
    $previousSecure = $_SERVER['SESSION_SECURE_COOKIE'] ?? null;
    $previousPutenv = getenv('SESSION_SECURE_COOKIE');

    $_SERVER['APP_ENV'] = $environment;
    unset($_SERVER['SESSION_SECURE_COOKIE'], $_ENV['SESSION_SECURE_COOKIE']);
    putenv('SESSION_SECURE_COOKIE');

    try {
        return require base_path('config/session.php');
    } finally {
        $_SERVER['APP_ENV'] = $previousEnv;

        if ($previousSecure !== null) {
            $_SERVER['SESSION_SECURE_COOKIE'] = $previousSecure;
        }

        if ($previousPutenv !== false) {
            putenv('SESSION_SECURE_COOKIE='.$previousPutenv);
        }
    }
}
