<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Tag;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

it('flushes the sitemap cache when a category changes', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());

    Category::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse();
});

it('flushes the sitemap cache when a tag changes', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());

    Tag::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse();
});

it('flushes the sitemap cache when a course changes', function () {
    Cache::put('sitemap.urls', ['cached'], now()->addHour());

    Course::factory()->create();

    expect(Cache::has('sitemap.urls'))->toBeFalse();
});

it('lists the indexable legal pages in the sitemap', function () {
    Cache::flush();

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('legal.mentions'), false)
        ->assertSee(route('legal.privacy'), false)
        ->assertSee(route('legal.cookies'), false)
        ->assertSee(route('legal.cgv'), false);
});

it('keeps legal pages indexable (no robots noindex)', function () {
    $this->get('/mentions-legales')
        ->assertOk()
        ->assertDontSee('noindex', false);
});

/**
 * Blade analyse le gabarit avec token_get_all() avant de le compiler. Un "<?"
 * littéral y ouvre un bloc PHP quand short_open_tag est actif — ce qui est le
 * cas du serveur de production — et Blade cesse alors de compiler ses
 * directives dans le fichier : la vue rendue part en erreur 500.
 *
 * Le réglage n'étant pas modifiable à l'exécution, on vérifie la source plutôt
 * que le rendu.
 */
it('keeps the sitemap views free of literal PHP open tags', function (string $view) {
    $source = file_get_contents(resource_path("views/{$view}"));

    expect(str_contains($source, '<'.'?'))->toBeFalse();
})->with(['sitemap.blade.php', 'sitemap-videos.blade.php']);
