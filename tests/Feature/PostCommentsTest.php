<?php

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays root comments on the article page', function () {
    $post = Post::factory()->create(['status' => 'published']);
    Comment::factory()->for($post)->create([
        'author_name' => 'Visiteur Test',
        'content' => 'Merci pour cet article très utile.',
        'status' => CommentStatus::Approved,
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Visiteur Test')
        ->assertSee('Merci pour cet article très utile.');
});

it('displays the author replies nested under their parent comment', function () {
    Settings::set('contact_email', 'laura@example.com');

    $post = Post::factory()->create(['status' => 'published']);
    $parent = Comment::factory()->for($post)->create([
        'author_name' => 'Visiteur',
        'status' => CommentStatus::Approved,
        'posted_at' => now()->subDay(),
    ]);
    Comment::factory()->for($post)->create([
        'parent_id' => $parent->id,
        'author_name' => 'Laura B.',
        'author_email' => 'laura@example.com',
        'content' => 'Merci pour votre message, ravie que cela vous aide.',
        'status' => CommentStatus::Approved,
        'posted_at' => now(),
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Merci pour votre message, ravie que cela vous aide.')
        ->assertSee('Auteure'); // badge identifié par l'e-mail de l'auteure
});

it('does not show the author badge for a visitor sharing the author first name', function () {
    Settings::set('contact_email', 'laura@example.com');

    $post = Post::factory()->create(['status' => 'published']);
    Comment::factory()->for($post)->create([
        'author_name' => 'Laura Imposteur',
        'author_email' => 'inconnu@gmail.com',
        'content' => 'Commentaire d\'un visiteur prénommé Laura.',
        'status' => CommentStatus::Approved,
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('Auteure');
});

it('counts replies in the comment total', function () {
    $post = Post::factory()->create(['status' => 'published']);
    $parent = Comment::factory()->for($post)->create(['status' => CommentStatus::Approved]);
    Comment::factory()->for($post)->count(2)->create([
        'parent_id' => $parent->id,
        'status' => CommentStatus::Approved,
    ]);

    // 1 racine + 2 réponses = 3
    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('3 commentaires');
});

it('does not show unapproved comments or their replies', function () {
    $post = Post::factory()->create(['status' => 'published']);
    Comment::factory()->for($post)->create([
        'content' => 'Spam à modérer',
        'status' => CommentStatus::Pending,
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('Spam à modérer');
});
