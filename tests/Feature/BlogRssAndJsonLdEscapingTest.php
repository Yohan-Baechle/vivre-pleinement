<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('escapes closing script tags inside JSON-LD so admin-editable content cannot break out of the script block', function () {
    $post = Post::factory()->create([
        'title' => 'Titre </script><script>alert(1)</script>',
    ]);

    $response = $this->get(route('blog.show', $post->slug));

    $response->assertOk();
    $response->assertDontSee('</script><script>alert(1)</script>', false);
});

it('neutralizes a literal ]]> sequence inside RSS CDATA content', function () {
    Post::factory()->create([
        'content' => 'Du texte avant ]]> puis après.',
        'excerpt' => 'Un extrait ]]> avec une fermeture CDATA.',
    ]);

    $response = $this->get(route('blog.rss'));

    $response->assertOk();
    $body = $response->getContent();

    // Une fermeture CDATA littérale non neutralisée casserait le flux XML.
    expect(substr_count($body, ']]>'))->toBe(substr_count($body, '<![CDATA['));
});
