<?php

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.youtube.oauth_client_id' => 'client-id-test',
        'services.youtube.oauth_client_secret' => 'client-secret-test',
    ]);
});

it('404s when the youtube oauth client is not configured', function () {
    config(['services.youtube.oauth_client_id' => null]);

    $this->get(route('youtube.oauth.redirect'))->assertNotFound();
    $this->get(route('youtube.oauth.callback'))->assertNotFound();
});

it('redirects to google with a state parameter stored in session', function () {
    $response = $this->get(route('youtube.oauth.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('state=');
    expect(session('youtube_oauth_state'))->not->toBeNull();
});

it('rejects the callback when the state does not match the session', function () {
    $this->withSession(['youtube_oauth_state' => 'expected-state']);

    $this->get(route('youtube.oauth.callback', ['code' => 'auth-code', 'state' => 'wrong-state']))
        ->assertStatus(400)
        ->assertSee('État OAuth invalide');
});

it('rejects the callback when no state was ever issued', function () {
    $this->get(route('youtube.oauth.callback', ['code' => 'auth-code', 'state' => 'anything']))
        ->assertStatus(400)
        ->assertSee('État OAuth invalide');
});

it('shows the escaped refresh token on a successful callback', function () {
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response([
            'access_token' => 'access-token',
            'refresh_token' => '<script>alert(1)</script>',
            'expires_in' => 3600,
        ]),
    ]);

    $this->withSession(['youtube_oauth_state' => 'expected-state']);

    $response = $this->get(route('youtube.oauth.callback', ['code' => 'auth-code', 'state' => 'expected-state']));

    $response->assertOk();
    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee(e('<script>alert(1)</script>'), false);
    expect(session('youtube_oauth_state'))->toBeNull();
});

it('rejects a replayed state after the callback already consumed it', function () {
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response([
            'access_token' => 'access-token',
            'refresh_token' => 'a-refresh-token',
            'expires_in' => 3600,
        ]),
    ]);

    $this->withSession(['youtube_oauth_state' => 'expected-state']);

    $this->get(route('youtube.oauth.callback', ['code' => 'auth-code', 'state' => 'expected-state']))->assertOk();

    $this->get(route('youtube.oauth.callback', ['code' => 'auth-code', 'state' => 'expected-state']))
        ->assertStatus(400);
});
