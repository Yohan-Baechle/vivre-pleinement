<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** Compte les balises <meta name="description"> et renvoie le content de la première. */
function metaDescription(string $html): array
{
    $count = substr_count($html, '<meta name="description"');
    preg_match('/<meta name="description" content="([^"]*)"/', $html, $m);

    return [$count, html_entity_decode($m[1] ?? '', ENT_QUOTES)];
}

$generic = 'Anxiété généralisée, phobies, TOC, burnout';

it('emits exactly one, non-generic meta description on key indexable pages', function () use ($generic) {
    $pages = [
        '/' => 'thérapie ACT',
        '/contact' => 'Contactez Laura Baechlé',
        '/blog' => 'Articles, outils et ressources',
    ];

    foreach ($pages as $url => $expected) {
        [$count, $content] = metaDescription($this->get($url)->assertOk()->getContent());

        expect($count)->toBe(1, "Doublon de meta description sur {$url}")
            ->and($content)->toContain($expected);

        if ($url !== '/') {
            expect($content)->not->toContain($generic, "La description générique fuit sur {$url}");
        }
    }
});

it('keeps every key page title under the Google display limit (~60 chars)', function () {
    $titles = [
        '/' => 60,
        '/contact' => 60,
        '/blog' => 65,
    ];

    foreach ($titles as $url => $max) {
        preg_match('/<title>([^<]*)<\/title>/', $this->get($url)->getContent(), $m);
        $len = mb_strlen(html_entity_decode($m[1] ?? '', ENT_QUOTES));

        expect($len)->toBeLessThanOrEqual($max, "Title trop long sur {$url} ({$len} car.)");
    }
});

it('renders the migrated seo_title verbatim, without a brand suffix', function () {
    Post::factory()->create([
        'slug' => 'agoraphobie',
        'status' => 'published',
        'title' => 'Agoraphobie',
        'seo_title' => 'Agoraphobie : se Libérer de ce Trouble Anxieux Invalidant',
        'seo_description' => 'Une description optimisée.',
    ]);

    $this->get('/blog/agoraphobie')
        ->assertOk()
        ->assertSee('<title>Agoraphobie : se Libérer de ce Trouble Anxieux Invalidant</title>', false)
        ->assertDontSee('Invalidant · Vivre Pleinement', false);
});

it('falls back to the title with a brand suffix when seo_title is empty', function () {
    Post::factory()->create([
        'slug' => 'sans-seo-title',
        'status' => 'published',
        'title' => 'Un article sans titre SEO',
        'seo_title' => null,
    ]);

    $this->get('/blog/sans-seo-title')
        ->assertOk()
        ->assertSee('<title>Un article sans titre SEO · Vivre Pleinement</title>', false);
});

it('emits a single, query-string-free canonical on an article', function () {
    Post::factory()->create(['slug' => 'burn-out', 'status' => 'published', 'seo_canonical' => null]);

    $html = $this->get('/blog/burn-out?utm_source=newsletter')->assertOk()->getContent();

    expect(substr_count($html, 'rel="canonical"'))->toBe(1)
        ->and($html)->toContain('<link rel="canonical" href="'.url('/blog/burn-out').'"')
        ->and($html)->not->toContain('utm_source');
});

it('uses the seo_description for the article og:description', function () {
    Post::factory()->create([
        'slug' => 'toc',
        'status' => 'published',
        'seo_description' => 'Description SEO courte et nette.',
        'excerpt' => 'Un extrait beaucoup plus long qui ne devrait pas servir de og:description.',
    ]);

    $this->get('/blog/toc')
        ->assertOk()
        ->assertSee('property="og:description" content="Description SEO courte et nette."', false);
});

it('emits exactly one canonical on the blog index, stripped of filter query strings', function () {
    $html = $this->get('/blog?sort=oldest')->assertOk()->getContent();

    preg_match('/<link rel="canonical" href="([^"]*)"/', $html, $m);

    expect(substr_count($html, 'rel="canonical"'))->toBe(1)
        ->and($m[1] ?? '')->toBe(route('blog.index'));
});

it('emits exactly one canonical on a category page, without query strings', function () {
    $category = Category::factory()->create(['slug' => 'angoisse-et-anxiete']);

    $html = $this->get('/blog/categorie/angoisse-et-anxiete?page=1')->assertOk()->getContent();

    expect(substr_count($html, 'rel="canonical"'))->toBe(1)
        ->and($html)->toContain('<link rel="canonical" href="'.route('blog.category', $category->slug).'"');
});

it('ignores a stale migrated seo_canonical and points to the new blog URL', function () {
    Post::factory()->create([
        'slug' => 'ergophobie-peur-du-travail',
        'status' => 'published',
        'seo_canonical' => 'https://vivre-pleinement.fr/ergophobie-peur-du-travail/',
    ]);

    $this->get('/blog/ergophobie-peur-du-travail')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('blog.show', 'ergophobie-peur-du-travail').'"', false)
        ->assertDontSee('vivre-pleinement.fr/ergophobie-peur-du-travail/', false);
});

it('ignores a stale migrated seo_schema_json and renders a clean Article schema', function () {
    Post::factory()->create([
        'slug' => 'schema-obsolete',
        'status' => 'published',
        'title' => 'Article test',
        'seo_schema_json' => [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'author' => ['name' => 'Laura B.'],
            'mainEntityOfPage' => ['@id' => 'https://vivre-pleinement.fr/schema-obsolete/'],
        ],
    ]);

    $html = $this->get('/blog/schema-obsolete')->assertOk()->getContent();

    expect($html)->toContain('"name":"Laura Baechlé"')
        ->and($html)->not->toContain('"name":"Laura B."')
        ->and($html)->toContain('"@id":"'.route('blog.show', 'schema-obsolete').'"');
});
