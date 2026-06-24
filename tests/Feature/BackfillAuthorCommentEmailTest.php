<?php

use App\Models\Comment;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Settings::set('contact_email', 'contact@vivre-pleinement.fr');
});

it("renseigne l'e-mail de l'auteure sur ses réponses sans e-mail", function () {
    $reply = Comment::factory()->create([
        'author_name' => 'Laura B.',
        'author_email' => null,
    ]);

    $this->artisan('comments:backfill-author-email')
        ->expectsOutputToContain('contact@vivre-pleinement.fr')
        ->assertSuccessful();

    expect($reply->fresh()->author_email)->toBe('contact@vivre-pleinement.fr');
    expect($reply->fresh()->isFromAuthor())->toBeTrue();
});

it('ne touche pas aux commentaires des visiteurs', function () {
    $visitor = Comment::factory()->create([
        'author_name' => 'Lorelei',
        'author_email' => null,
    ]);

    $this->artisan('comments:backfill-author-email')->assertSuccessful();

    expect($visitor->fresh()->author_email)->toBeNull();
});

it('ne touche pas aux réponses ayant déjà un e-mail', function () {
    $reply = Comment::factory()->create([
        'author_name' => 'Laura B.',
        'author_email' => 'autre@example.com',
    ]);

    $this->artisan('comments:backfill-author-email')->assertSuccessful();

    expect($reply->fresh()->author_email)->toBe('autre@example.com');
});

it('en dry-run, ne modifie rien', function () {
    $reply = Comment::factory()->create([
        'author_name' => 'Laura B.',
        'author_email' => null,
    ]);

    $this->artisan('comments:backfill-author-email --dry-run')
        ->expectsOutputToContain('#'.$reply->id)
        ->assertSuccessful();

    expect($reply->fresh()->author_email)->toBeNull();
});
