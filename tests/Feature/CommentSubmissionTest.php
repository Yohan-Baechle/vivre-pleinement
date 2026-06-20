<?php

use App\Enums\CommentStatus;
use App\Mail\NewCommentNotification;
use App\Models\Comment;
use App\Models\Post;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('comment:127.0.0.1');
    Settings::set('comments_enabled', '1');
    Settings::set('notify_email', 'laura@example.com');
});

function validCommentPayload(array $overrides = []): array
{
    return array_merge([
        'author_name' => 'Jean Visiteur',
        'author_email' => 'jean@gmail.com',
        'content' => 'Merci pour cet article, il m\'a beaucoup aidé.',
        'consent' => '1',
        'website' => '',
        'ts' => time() - 5,
    ], $overrides);
}

it('stores a submitted comment as pending (not visible until approved)', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload())
        ->assertRedirect();

    $comment = Comment::first();
    expect($comment)->not->toBeNull()
        ->status->toBe(CommentStatus::Pending)
        ->author_name->toBe('Jean Visiteur');
});

it('notifies the moderator by email', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload());

    Mail::assertQueued(NewCommentNotification::class, fn ($mail) => $mail->hasTo('laura@example.com'));
});

it('does not show a pending comment on the public page', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);
    Comment::factory()->for($post)->create([
        'content' => 'Commentaire en attente secret',
        'status' => CommentStatus::Pending,
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('Commentaire en attente secret');
});

it('rejects a submission caught by the honeypot', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload(['website' => 'http://spam.test']))
        ->assertSessionHasErrors('website');

    expect(Comment::count())->toBe(0);
});

it('rejects a submission sent too fast', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload(['ts' => time()]))
        ->assertSessionHasErrors('ts');

    expect(Comment::count())->toBe(0);
});

it('requires consent', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload(['consent' => null]))
        ->assertSessionHasErrors('consent');

    expect(Comment::count())->toBe(0);
});

it('blocks submission when comments are disabled on the post', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => false]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload())
        ->assertForbidden();

    expect(Comment::count())->toBe(0);
});

it('blocks submission when comments are globally disabled', function () {
    Settings::set('comments_enabled', '0');
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->post(route('blog.comments.store', $post->slug), validCommentPayload())
        ->assertForbidden();

    expect(Comment::count())->toBe(0);
});

it('shows the comment form when comments are open', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => true]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Laisser un commentaire');
});

it('hides the comment form when comments are closed on the post', function () {
    $post = Post::factory()->create(['status' => 'published', 'comments_enabled' => false]);

    $this->get(route('blog.show', $post->slug))
        ->assertOk()
        ->assertDontSee('Laisser un commentaire');
});
