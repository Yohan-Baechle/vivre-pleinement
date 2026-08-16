<?php

use App\Mail\ContactMessage;
use App\Models\Post;
use App\Support\SubmissionStamp;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('contact:127.0.0.1');
    RateLimiter::clear('comment:127.0.0.1');
});

/**
 * L'horodatage anti-robot voyage dans un champ caché : en clair, n'importe quel
 * script postait `ts = time() - 10` et franchissait le garde-fou sans effort.
 */
it('rejette un horodatage forgé en clair', function () {
    $this->post(route('contact.send'), stampedContactPayload(['ts' => (string) (time() - 60)]))
        ->assertSessionHasErrors('ts');

    Mail::assertNothingQueued();
});

it('rejette un horodatage chiffré avec une autre clé', function () {
    $encrypter = new Encrypter(Encrypter::generateKey('aes-256-cbc'), 'aes-256-cbc');

    $this->post(route('contact.send'), stampedContactPayload([
        'ts' => $encrypter->encryptString((string) (time() - 60)),
    ]))->assertSessionHasErrors('ts');

    Mail::assertNothingQueued();
});

it('accepte un horodatage émis par le site et suffisamment ancien', function () {
    $this->post(route('contact.send'), stampedContactPayload())
        ->assertRedirect(route('contact.thanks'));

    Mail::assertQueued(ContactMessage::class, 1);
});

it('rejette une soumission instantanée', function () {
    $this->post(route('contact.send'), stampedContactPayload(['ts' => SubmissionStamp::issue()]))
        ->assertSessionHasErrors('ts');

    Mail::assertNothingQueued();
});

it('protège aussi le formulaire de commentaires', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), [
        'author_name' => 'Camille',
        'author_email' => 'camille@gmail.com',
        'content' => 'Merci pour cet article, il m\'a beaucoup aidée.',
        'consent' => '1',
        'ts' => (string) (time() - 60),
    ])->assertSessionHasErrors('ts');

    expect($post->comments()->count())->toBe(0);
});

it('émet un horodatage relisible uniquement par l\'application', function () {
    expect(SubmissionStamp::read(SubmissionStamp::issue()))->toBeGreaterThan(0)
        ->and(SubmissionStamp::read('42'))->toBeNull()
        ->and(SubmissionStamp::read(''))->toBeNull()
        ->and(SubmissionStamp::read(null))->toBeNull();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function stampedContactPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Camille',
        'email' => 'camille@gmail.com',
        'subject' => 'question',
        'message' => 'Bonjour, je souhaiterais en savoir plus sur votre accompagnement.',
        'consent' => '1',
        'website' => '',
        'ts' => submissionStamp(),
    ], $overrides);
}
