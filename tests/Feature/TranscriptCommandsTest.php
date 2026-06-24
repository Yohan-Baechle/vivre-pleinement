<?php

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

afterEach(function () {
    File::deleteDirectory(storage_path('app/testing-transcripts'));
});

function transcriptPath(string $name): string
{
    $path = storage_path('app/testing-transcripts/'.$name);
    File::ensureDirectoryExists(dirname($path));

    return $path;
}

it('exports a raw transcript split into word-bounded chunks', function () {
    $words = collect(range(1, 2500))->map(fn ($n) => 'mot'.$n)->implode(' ');
    Video::factory()->create(['duration_seconds' => 600, 'transcript' => '<p>'.$words.'</p>']);

    $path = transcriptPath('raw.json');
    $this->artisan('videos:export-transcripts', ['path' => $path, '--chunk-words' => 1000])
        ->assertSuccessful();

    $payload = json_decode(File::get($path), true);

    expect($payload['videos'])->toHaveCount(1)
        ->and($payload['videos'][0]['chunks'])->toHaveCount(3); // 2500 / 1000 = 3
});

it('skips videos without a transcript on export', function () {
    Video::factory()->create(['duration_seconds' => 600, 'transcript' => null]);

    $path = transcriptPath('raw.json');
    $this->artisan('videos:export-transcripts', ['path' => $path])->assertSuccessful();

    expect(json_decode(File::get($path), true)['videos'] ?? [])->toHaveCount(0);
});

it('imports repunctuated chunks and reassembles them into the transcript', function () {
    $video = Video::factory()->create(['duration_seconds' => 600, 'transcript' => '<p>brut</p>']);

    $path = transcriptPath('done.json');
    File::put($path, json_encode(['videos' => [[
        'id' => $video->id,
        'chunks' => [
            '<p>Première partie ponctuée.</p>',
            '<p>Deuxième partie ponctuée.</p>',
        ],
    ]]]));

    $this->artisan('videos:import-transcripts', ['path' => $path])->assertSuccessful();

    $transcript = $video->fresh()->transcript;

    expect($transcript)->toContain('Première partie ponctuée.')
        ->and($transcript)->toContain('Deuxième partie ponctuée.')
        ->and(substr_count($transcript, '<p>'))->toBe(2);
});

it('strips disallowed tags from imported chunks', function () {
    $video = Video::factory()->create(['duration_seconds' => 600, 'transcript' => null]);

    $path = transcriptPath('done.json');
    File::put($path, json_encode(['videos' => [[
        'id' => $video->id,
        'chunks' => ['<p>Texte <script>alert(1)</script> propre.</p>'],
    ]]]));

    $this->artisan('videos:import-transcripts', ['path' => $path])->assertSuccessful();

    expect($video->fresh()->transcript)
        ->toContain('Texte')
        ->not->toContain('<script>');
});

it('does not write in dry-run mode', function () {
    $video = Video::factory()->create(['duration_seconds' => 600, 'transcript' => '<p>original</p>']);

    $path = transcriptPath('done.json');
    File::put($path, json_encode(['videos' => [[
        'id' => $video->id,
        'chunks' => ['<p>nouveau</p>'],
    ]]]));

    $this->artisan('videos:import-transcripts', ['path' => $path, '--dry-run' => true])->assertSuccessful();

    expect($video->fresh()->transcript)->toBe('<p>original</p>');
});
