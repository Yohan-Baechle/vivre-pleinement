<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('prepends the definition opening and seeds the FAQ', function () {
    $post = Post::factory()->create(['slug' => 'etre-casanier', 'status' => 'published', 'content' => '<p>Contenu existant.</p>']);

    $this->artisan('posts:apply-snippet-openings')->assertSuccessful();

    $post->refresh();

    expect($post->content)->toContain('key-answer')
        ->and($post->content)->toContain('que veut dire « casanier »')
        ->and($post->content)->toContain('<p>Contenu existant.</p>')
        ->and($post->seo_title)->toContain('définition')
        ->and(mb_strlen($post->seo_description))->toBeLessThanOrEqual(155)
        ->and($post->faq)->toHaveCount(4);
});

it('does not duplicate the opening when run twice', function () {
    $post = Post::factory()->create(['slug' => 'rancune-et-rancoeur', 'status' => 'published', 'content' => '<p>Contenu.</p>']);

    $this->artisan('posts:apply-snippet-openings')->assertSuccessful();
    $this->artisan('posts:apply-snippet-openings')->assertSuccessful();

    expect(substr_count($post->refresh()->content, 'key-answer'))->toBe(1);
});

it('renders the FAQ accordion and FAQPage schema on the article page', function () {
    Post::factory()->create(['slug' => 'etre-casanier', 'status' => 'published', 'content' => '<p>Contenu.</p>']);
    $this->artisan('posts:apply-snippet-openings')->assertSuccessful();

    $html = $this->get(route('blog.show', 'etre-casanier'))->assertOk()->getContent();

    expect($html)->toContain('"@type":"FAQPage"')
        ->and($html)->toContain('Vos questions sur le sujet')
        ->and($html)->toContain('une personne casanière ?');
});

it('renders no FAQ section when the post has none', function () {
    $post = Post::factory()->create(['status' => 'published']);

    $html = $this->get(route('blog.show', $post->slug))->assertOk()->getContent();

    expect($html)->not->toContain('FAQPage')
        ->and($html)->not->toContain('Vos questions sur le sujet');
});
