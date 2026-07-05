<?php

use App\Mail\ContactMessage;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('contact:127.0.0.1');
});

function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Jeanne',
        'last_name' => 'Visiteuse',
        'email' => 'jeanne@gmail.com',
        'phone' => '0601020304',
        'subject' => 'question',
        'message' => 'Bonjour, j\'aimerais en savoir plus sur votre accompagnement.',
        'consent' => '1',
        'website' => '',
        'ts' => time() - 5,
    ], $overrides);
}

it('envoie le message de contact et redirige vers la page de remerciement', function () {
    $this->post(route('contact.send'), validContactPayload())
        ->assertRedirect(route('contact.thanks'));

    Mail::assertQueued(ContactMessage::class, 1);
});

it('envoie le message à l\'adresse de notification configurée dans l\'admin', function () {
    Settings::set('notify_email', 'laura@example.com');

    $this->post(route('contact.send'), validContactPayload());

    Mail::assertQueued(ContactMessage::class, fn ($mail) => $mail->hasTo('laura@example.com'));
});

it('refuse un message sans consentement', function () {
    $this->post(route('contact.send'), validContactPayload(['consent' => '']))
        ->assertSessionHasErrors('consent');

    Mail::assertNothingQueued();
});

it('refuse un message trop court', function () {
    $this->post(route('contact.send'), validContactPayload(['message' => 'Trop court.']))
        ->assertSessionHasErrors('message');

    Mail::assertNothingQueued();
});

it('rejette une soumission dont le honeypot est rempli', function () {
    $this->post(route('contact.send'), validContactPayload(['website' => 'https://spam.example']))
        ->assertSessionHasErrors('website');

    Mail::assertNothingQueued();
});

it('rejette une soumission trop rapide (honeypot temporel)', function () {
    $this->post(route('contact.send'), validContactPayload(['ts' => time()]))
        ->assertSessionHasErrors();

    Mail::assertNothingQueued();
});

it('refuse un objet hors de la liste autorisée', function () {
    $this->post(route('contact.send'), validContactPayload(['subject' => 'hacking']))
        ->assertSessionHasErrors('subject');

    Mail::assertNothingQueued();
});
