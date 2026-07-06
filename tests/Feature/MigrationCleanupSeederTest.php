<?php

use App\Console\Commands\CleanCommentContent;
use App\Models\Comment;
use App\Models\Post;
use Database\Seeders\MigrationCleanupSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('exposes a static clean() method matching the sibling cleanup commands', function () {
    expect(CleanCommentContent::clean('<p>Bonjour</p>'))->toBe('Bonjour');
});

it('runs without error and cleans comment/post content', function () {
    $comment = Comment::factory()->create(['content' => '<p>Bonjour</p>', 'author_name' => 'Laura J.']);
    $post = Post::factory()->create(['content' => '<span>Texte</span>']);

    (new MigrationCleanupSeeder)->run();

    expect($comment->fresh()->content)->toBe('Bonjour')
        ->and($comment->fresh()->author_name)->toBe('Laura B.')
        ->and($post->fresh()->content)->toBe('Texte');
});
