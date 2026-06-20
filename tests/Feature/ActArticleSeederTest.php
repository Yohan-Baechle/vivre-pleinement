<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Database\Seeders\ActArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Le seeder rattache l'article à cette catégorie : on s'assure qu'elle existe
    // (firstOrCreate car la base de test peut déjà la contenir).
    Category::firstOrCreate(
        ['slug' => 'comprendre-et-se-soigner'],
        ['name' => 'Comprendre & se soigner'],
    );
});

it('crée un article ACT publié avec sa catégorie et ses tags', function () {
    $this->seed(ActArticleSeeder::class);

    $post = Post::where('slug', 'therapie-act')->first();

    expect($post)->not->toBeNull()
        ->and($post->status)->toBe(PostStatus::Published)
        ->and($post->seo_title)->not->toBeEmpty()
        ->and($post->seo_description)->not->toBeEmpty()
        ->and(str_word_count(strip_tags($post->content)))->toBeGreaterThan(2000)
        ->and($post->categories->pluck('slug'))->toContain('comprendre-et-se-soigner')
        ->and($post->tags->pluck('slug'))->toContain('therapie-act');
});

it('est idempotent : un second passage ne duplique pas l\'article', function () {
    $this->seed(ActArticleSeeder::class);
    $this->seed(ActArticleSeeder::class);

    expect(Post::where('slug', 'therapie-act')->count())->toBe(1);
});

it('rend la page article avec un statut 200', function () {
    $this->seed(ActArticleSeeder::class);

    $this->get('/blog/therapie-act')
        ->assertOk()
        ->assertSee('thérapie ACT', false);
});
