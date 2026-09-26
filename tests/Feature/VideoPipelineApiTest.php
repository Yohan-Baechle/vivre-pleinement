<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config(['services.automation.token' => 'secret-token']);
});

function automation(): array
{
    return ['Authorization' => 'Bearer secret-token'];
}

/**
 * @return array<string, mixed>
 */
function decodePayload(string $payload): array
{
    return json_decode(gzdecode(base64_decode($payload)), true);
}

/**
 * @return array<string, mixed>
 */
function enrichmentBody(array $overrides = []): array
{
    return array_merge([
        'intro' => '<p>Une intro.</p><script>alert(1)</script>',
        'summary' => 'Un résumé.',
        'seo_description' => 'Une description SEO.',
        'key_takeaways' => [
            ['title' => 'Point clé', 'content' => 'Son détail.'],
        ],
        'chapters' => [],
        'category_slugs' => ['phobies', 'inconnue'],
    ], $overrides);
}

it('refuses every request when no token is configured', function () {
    config(['services.automation.token' => null]);

    $this->getJson(
        route('automation.videos.transcripts.pending'),
        automation(),
    )->assertForbidden();
});

it('rejects a wrong or missing bearer token', function (array $headers) {
    $this->getJson(route('automation.videos.transcripts.pending'), $headers)
        ->assertUnauthorized();
})->with([
    'missing' => [[]],
    'wrong' => [['Authorization' => 'Bearer nope']],
]);

it('lists raw transcripts split into encoded chunks', function () {
    Video::factory()->withRawTranscript(2500)->create(['title' => 'Brute']);
    Video::factory()->withFormattedTranscript()->create();
    Video::factory()->short()->withRawTranscript()->create();

    $response = $this->getJson(
        route('automation.videos.transcripts.pending', ['limit' => 5]),
        automation(),
    )->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Brute')
        ->and($response->json('data.0.chunks'))->toHaveCount(3);

    $first = decodePayload($response->json('data.0.chunks.0'));

    expect($first)
        ->toMatchArray(['title' => 'Brute', 'part' => 1, 'parts' => 3])
        ->and(str_word_count($first['text']))->toBe(1200);
});

it('stores a formatted transcript and marks it as formatted', function () {
    $video = Video::factory()->create([
        'transcript' => '<p>un deux trois quatre cinq six</p>',
    ]);

    $this->putJson(route('automation.videos.transcript.store', $video), [
        'chunks' => ['<p>Un, deux, trois.</p>', '<p>Quatre, cinq, six.</p>'],
    ], automation())
        ->assertSuccessful()
        ->assertJson(['words' => 6, 'paragraphs' => 2]);

    $video->refresh();

    expect($video->transcript)->toContain('<p>Quatre, cinq, six.</p>')
        ->and($video->transcript_formatted_at)->not->toBeNull();
});

it('rejects a formatted transcript that lost words', function () {
    $video = Video::factory()->withRawTranscript(100)->create();

    $this->putJson(route('automation.videos.transcript.store', $video), [
        'chunks' => ['<p>Trop court.</p>'],
    ], automation())->assertUnprocessable();

    expect($video->fresh()->transcript_formatted_at)->toBeNull();
});

it('lists formatted videos awaiting enrichment with categories', function () {
    $phobies = Category::query()->firstOrCreate(
        ['slug' => 'phobies'],
        ['name' => 'Phobies'],
    );
    Video::factory()->withFormattedTranscript()->create(['title' => 'À faire']);
    Video::factory()->withFormattedTranscript()->create(['summary' => 'Fait']);
    Video::factory()->withRawTranscript()->create();

    $response = $this->getJson(
        route('automation.videos.enrichments.pending', ['limit' => 5]),
        automation(),
    )->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);

    $payload = decodePayload($response->json('data.0.payload'));

    expect($payload['title'])->toBe('À faire')
        ->and($payload['transcript'])->not->toContain('<p>')
        ->and($payload['available_categories'])
        ->toContain(['slug' => 'phobies', 'name' => $phobies->name]);
});

it('stores the enrichment and sanitizes the intro', function () {
    $category = Category::query()->firstOrCreate(
        ['slug' => 'phobies'],
        ['name' => 'Phobies'],
    );
    $video = Video::factory()->withFormattedTranscript()->create();

    $this->putJson(
        route('automation.videos.enrichment.store', $video),
        enrichmentBody(),
        automation(),
    )
        ->assertSuccessful()
        ->assertJson([
            'public_url' => route('videos.show', $video),
            'unknown_categories' => ['inconnue'],
        ]);

    $video->refresh();

    expect($video->intro)->toBe('<p>Une intro.</p>alert(1)')
        ->and($video->summary)->toBe('Un résumé.')
        ->and($video->key_takeaways)->toHaveCount(1)
        ->and($video->categories->pluck('id')->all())->toBe([$category->id]);
});

it('never overwrites an already enriched video', function () {
    $video = Video::factory()->withFormattedTranscript()->create([
        'summary' => 'Rédigé à la main',
    ]);

    $this->putJson(
        route('automation.videos.enrichment.store', $video),
        enrichmentBody(),
        automation(),
    )->assertConflict();

    expect($video->fresh()->summary)->toBe('Rédigé à la main');
});

it('validates the enrichment body', function () {
    $video = Video::factory()->withFormattedTranscript()->create();

    $this->putJson(
        route('automation.videos.enrichment.store', $video),
        enrichmentBody(['summary' => '', 'key_takeaways' => []]),
        automation(),
    )->assertJsonValidationErrors(['summary', 'key_takeaways']);
});

it('schedules the youtube sync and the transcript fetch', function () {
    $commands = collect(app(Schedule::class)->events())
        ->map(fn ($event): string => (string) $event->command);

    expect($commands->contains(fn (string $command): bool => str_ends_with(
        $command,
        'youtube:sync',
    )))->toBeTrue()
        ->and($commands->contains(fn (string $command): bool => str_ends_with(
            $command,
            'youtube:fetch-transcripts --since=14',
        )))->toBeTrue();
});
