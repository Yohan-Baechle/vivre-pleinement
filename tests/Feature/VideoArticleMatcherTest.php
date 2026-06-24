<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Video;
use App\Support\VideoArticleMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function categorizedVideo(Category $c, array $attrs = []): Video
{
    $video = Video::factory()->create([...['duration_seconds' => 600], ...$attrs]);
    $video->categories()->attach($c);

    return $video;
}

function categorizedPost(Category $c, array $attrs = []): Post
{
    $post = Post::factory()->create($attrs);
    $post->categories()->attach($c);

    return $post;
}

it('matches a video to a post sharing a specific keyword', function () {
    $c = Category::factory()->create();
    $post = categorizedPost($c, ['title' => 'Vaincre la cardiophobie']);
    $relevant = categorizedVideo($c, ['title' => 'La cardiophobie expliquée', 'view_count' => 5]);
    categorizedVideo($c, ['title' => 'Les antidépresseurs', 'view_count' => 99999]);

    expect(VideoArticleMatcher::videoForPost($post)?->id)->toBe($relevant->id);
});

it('returns null when only generic words overlap', function () {
    $c = Category::factory()->create();
    // Partagent seulement "vie" (mot vide) → pas assez pertinent.
    $post = categorizedPost($c, ['title' => 'Quel est le but de la vie']);
    categorizedVideo($c, ['title' => 'Vivre en accord avec ses valeurs pour une belle vie']);

    expect(VideoArticleMatcher::videoForPost($post))->toBeNull();
});

it('returns null when only the word peur overlaps between two different phobias', function () {
    $c = Category::factory()->create();
    $post = categorizedPost($c, ['title' => 'Ergophobie : la peur du travail']);
    categorizedVideo($c, ['title' => 'La peur de conduire sur autoroute']);

    expect(VideoArticleMatcher::videoForPost($post))->toBeNull();
});

it('prefers the explicit related post over a category match', function () {
    $c = Category::factory()->create();
    $explicitPost = categorizedPost($c, ['title' => 'Article sans rapport lexical']);
    categorizedPost($c, ['title' => 'La cardiophobie en détail']);

    $video = categorizedVideo($c, ['title' => 'Vaincre la cardiophobie', 'related_post_id' => $explicitPost->id]);
    $video->load('relatedPost');

    expect(VideoArticleMatcher::postForVideo($video)?->id)->toBe($explicitPost->id);
});

it('matches a post to a video in the reverse direction', function () {
    $c = Category::factory()->create();
    $video = categorizedVideo($c, ['title' => 'Sortir du burn-out']);
    $relevant = categorizedPost($c, ['title' => 'Le burn-out et comment le surmonter']);
    categorizedPost($c, ['title' => 'La méditation du matin']);

    expect(VideoArticleMatcher::postForVideo($video)?->id)->toBe($relevant->id);
});

it('returns null when the post or video has no category', function () {
    $post = Post::factory()->create(['title' => 'Un article sans catégorie']);
    $video = Video::factory()->create(['title' => 'Une vidéo sans catégorie', 'duration_seconds' => 600]);

    expect(VideoArticleMatcher::videoForPost($post))->toBeNull()
        ->and(VideoArticleMatcher::postForVideo($video))->toBeNull();
});
