<?php

use App\Models\Comment;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('sanitizes paragraph tags, line breaks and repeated spaces from comment content', function () {
    $comment = Comment::factory()->create([
        'content' => '<p>Bonjour,</p><p>Merci   beaucoup !<br>À bientôt.</p>',
    ]);

    $this->artisan('comments:clean-content')
        ->assertSuccessful()
        ->expectsOutputToContain('1 commentaire(s) nettoyé(s).');

    expect($comment->fresh()->content)
        ->toBe("Bonjour,\n\nMerci beaucoup !\nÀ bientôt.");
});

it('leaves already-clean comments untouched and reports zero changes', function () {
    Comment::factory()->create(['content' => 'Déjà propre, sans balises.']);

    $this->artisan('comments:clean-content')
        ->assertSuccessful()
        ->expectsOutputToContain('0 commentaire(s) nettoyé(s).');
});

it('does not persist changes in dry-run mode', function () {
    $comment = Comment::factory()->create([
        'content' => '<p>Contenu &amp; balises.</p>',
    ]);

    $this->artisan('comments:clean-content', ['--dry-run' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('1 commentaire(s) à nettoyer.');

    expect($comment->fresh()->content)->toBe('<p>Contenu &amp; balises.</p>');
});
