<?php

use App\Jobs\SubscribeToNewsletterJob;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('newsletter:127.0.0.1');
    config([
        'services.brevo.key' => 'test-key',
        'services.brevo.video_list_id' => 6,
        'services.brevo.doi_template_id' => 6,
    ]);
});

function validNewsletterPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Camille',
        'email' => 'camille@gmail.com',
        'website' => '',
        'ts' => submissionStamp(),
    ], $overrides);
}

it('subscribes a contact via the Brevo double opt-in endpoint', function () {
    Http::fake(['api.brevo.com/v3/contacts/doubleOptinConfirmation' => Http::response('', 201)]);

    $this->post(route('newsletter.store'), validNewsletterPayload())
        ->assertRedirect()
        ->assertSessionHas('newsletter_status', 'pending');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation'
            && $request['email'] === 'camille@gmail.com'
            && $request['attributes']['PRENOM'] === 'Camille'
            && $request['includeListIds'] === [6]
            && $request['templateId'] === 6
            && str_contains($request['redirectionUrl'], '/inscription-confirmee');
    });
});

it('dispatches the subscription onto the queue instead of calling Brevo synchronously', function () {
    Queue::fake();

    $this->post(route('newsletter.store'), validNewsletterPayload())
        ->assertRedirect()
        ->assertSessionHas('newsletter_status', 'pending');

    Queue::assertPushed(SubscribeToNewsletterJob::class, function (SubscribeToNewsletterJob $job) {
        return $job->email === 'camille@gmail.com'
            && $job->firstName === 'Camille'
            && str_contains($job->redirectionUrl, '/inscription-confirmee');
    });
});

it('returns JSON when subscribing via an AJAX request', function () {
    Http::fake(['api.brevo.com/v3/contacts/doubleOptinConfirmation' => Http::response('', 201)]);

    $this->postJson(route('newsletter.store'), validNewsletterPayload())
        ->assertOk()
        ->assertJson(['status' => 'pending']);
});

it('returns JSON errors when an AJAX subscription fails', function () {
    Http::fake(['api.brevo.com/v3/contacts/doubleOptinConfirmation' => Http::response('', 400)]);

    $this->postJson(route('newsletter.store'), validNewsletterPayload())
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['email']]);
});

it('shows an error when Brevo rejects the subscription', function () {
    Http::fake(['api.brevo.com/v3/contacts/doubleOptinConfirmation' => Http::response('', 400)]);

    $this->post(route('newsletter.store'), validNewsletterPayload())
        ->assertSessionHasErrors('email');
});

it('shows an error when Brevo is unreachable', function () {
    Http::fake(fn () => throw new ConnectionException('timeout'));

    $this->post(route('newsletter.store'), validNewsletterPayload())
        ->assertSessionHasErrors('email');
});

it('rejects a submission caught by the honeypot', function () {
    Http::fake();

    $this->post(route('newsletter.store'), validNewsletterPayload(['website' => 'http://spam.test']))
        ->assertSessionHasErrors('website');

    Http::assertNothingSent();
});

it('rejects a submission sent too fast', function () {
    Http::fake();

    $this->post(route('newsletter.store'), validNewsletterPayload(['ts' => submissionStamp(0)]))
        ->assertSessionHasErrors('ts');

    Http::assertNothingSent();
});

it('requires a valid email', function () {
    Http::fake();

    $this->post(route('newsletter.store'), validNewsletterPayload(['email' => 'not-an-email']))
        ->assertSessionHasErrors('email');

    Http::assertNothingSent();
});

it('renders the confirmation page', function () {
    $this->get(route('newsletter.confirmed'))
        ->assertOk()
        ->assertSee('Inscription confirmée');
});
