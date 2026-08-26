<?php

use App\Mail\ContactMessage;
use App\Models\Course;
use App\Support\Settings;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(LazilyRefreshDatabase::class);

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
        'ts' => submissionStamp(),
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
    $this->post(route('contact.send'), validContactPayload(['ts' => submissionStamp(0)]))
        ->assertSessionHasErrors();

    Mail::assertNothingQueued();
});

it('refuse un objet hors de la liste autorisée', function () {
    $this->post(route('contact.send'), validContactPayload(['subject' => 'hacking']))
        ->assertSessionHasErrors('subject');

    Mail::assertNothingQueued();
});

/**
 * Le motif « formation » suit la même règle que l'entrée de menu et la
 * connexion élève : sans catalogue, il n'aurait aucun objet.
 */
it('ne propose pas le motif formation quand aucune n\'est publiée', function () {
    Course::query()->forceDelete();

    $this->get(route('contact'))
        ->assertOk()
        ->assertDontSee('Question sur une formation', false);
});

it('propose le motif formation dès qu\'une formation est publiée', function () {
    Course::factory()->create();

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Question sur une formation', false);
});

it('refuse le motif formation tant qu\'aucune n\'est publiée', function () {
    Course::query()->forceDelete();

    $this->post(route('contact.send'), validContactPayload(['subject' => 'formation']))
        ->assertSessionHasErrors('subject');

    Mail::assertNothingQueued();
});

it('accepte le motif formation une fois une formation publiée', function () {
    Course::factory()->create();

    $this->post(route('contact.send'), validContactPayload(['subject' => 'formation']))
        ->assertRedirect(route('contact.thanks'));

    Mail::assertQueued(ContactMessage::class);
});
