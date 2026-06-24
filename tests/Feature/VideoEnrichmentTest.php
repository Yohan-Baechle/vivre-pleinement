<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

function writeEnrichmentFile(array $videos): string
{
    $path = storage_path('app/testing/enrichment-'.uniqid().'.json');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode(['videos' => $videos]));

    return $path;
}

afterEach(function () {
    File::deleteDirectory(storage_path('app/testing'));
});

it('exports unenriched videos to a json file', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    Video::factory()->create(['duration_seconds' => 600, 'title' => 'Peur de conduire']);

    $path = storage_path('app/testing/export.json');

    $this->artisan('videos:export-enrichment', ['path' => $path])
        ->assertSuccessful();

    $payload = json_decode(File::get($path), true);

    expect($payload['videos'])->toHaveCount(1)
        ->and($payload['videos'][0]['title'])->toBe('Peur de conduire')
        ->and($payload['_available_categories'])->toContain(['slug' => 'phobies', 'name' => 'Phobies']);
});

it('skips already enriched videos unless --all is passed', function () {
    Video::factory()->create(['duration_seconds' => 600, 'summary' => 'Déjà fait']);

    $path = storage_path('app/testing/export.json');

    $this->artisan('videos:export-enrichment', ['path' => $path])->assertSuccessful();
    expect(json_decode(File::get($path), true)['videos'])->toHaveCount(0);

    $this->artisan('videos:export-enrichment', ['path' => $path, '--all' => true])->assertSuccessful();
    expect(json_decode(File::get($path), true)['videos'])->toHaveCount(1);
});

it('imports editorial content and assigns categories', function () {
    Category::query()->firstOrCreate(['slug' => 'phobies'], ['name' => 'Phobies']);
    $video = Video::factory()->create(['duration_seconds' => 600]);

    $path = writeEnrichmentFile([[
        'id' => $video->id,
        'category_slugs' => ['phobies'],
        'intro' => '<p>Une intro riche.</p>',
        'summary' => 'Un résumé.',
        'seo_description' => 'Une meta description.',
        'key_takeaways' => [
            ['title' => 'Point un', 'content' => 'Détail.'],
            ['title' => '', 'content' => 'À ignorer'],
        ],
        'chapters' => [
            ['title' => 'Intro', 'start_seconds' => 0],
            ['title' => '', 'start_seconds' => 50],
        ],
    ]]);

    $this->artisan('videos:import-enrichment', ['path' => $path])->assertSuccessful();

    $video->refresh();

    expect($video->intro)->toBe('<p>Une intro riche.</p>')
        ->and($video->summary)->toBe('Un résumé.')
        ->and($video->seo_description)->toBe('Une meta description.')
        ->and($video->key_takeaways)->toHaveCount(1)
        ->and($video->key_takeaways[0]['title'])->toBe('Point un')
        ->and($video->chapters)->toHaveCount(1)
        ->and($video->categories->pluck('slug')->all())->toBe(['phobies']);
});

it('does not write anything in dry-run mode', function () {
    $video = Video::factory()->create(['duration_seconds' => 600, 'summary' => null]);

    $path = writeEnrichmentFile([[
        'id' => $video->id,
        'summary' => 'Ne doit pas être écrit',
    ]]);

    $this->artisan('videos:import-enrichment', ['path' => $path, '--dry-run' => true])->assertSuccessful();

    expect($video->fresh()->summary)->toBeNull();
});

it('warns about unknown category slugs but still imports the rest', function () {
    $video = Video::factory()->create(['duration_seconds' => 600]);

    $path = writeEnrichmentFile([[
        'id' => $video->id,
        'category_slugs' => ['inexistante'],
        'summary' => 'Un résumé.',
    ]]);

    $this->artisan('videos:import-enrichment', ['path' => $path])->assertSuccessful();

    expect($video->fresh()->summary)->toBe('Un résumé.')
        ->and($video->categories)->toHaveCount(0);
});

it('fails gracefully when the file is missing', function () {
    $this->artisan('videos:import-enrichment', ['path' => storage_path('app/testing/nope.json')])
        ->assertFailed();
});

it('renders the intro above the video on the show page', function () {
    $video = Video::factory()->create([
        'slug' => 'avec-intro',
        'duration_seconds' => 600,
        'intro' => '<p>Texte introductif indexable.</p>',
    ]);

    $response = $this->get('/videos/avec-intro')->assertOk();

    $html = $response->getContent();
    $introPos = strpos($html, 'Texte introductif indexable');
    $videoPos = strpos($html, 'youtube-facade');

    expect($introPos)->not->toBeFalse()
        ->and($videoPos)->not->toBeFalse()
        ->and($introPos)->toBeLessThan($videoPos);
});
