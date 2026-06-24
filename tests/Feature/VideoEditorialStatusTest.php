<?php

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('reports a video as enriched only when intro and summary are both present', function () {
    expect(Video::factory()->make(['intro' => '<p>x</p>', 'summary' => 'y'])->isEnriched())->toBeTrue()
        ->and(Video::factory()->make(['intro' => '<p>x</p>', 'summary' => null])->isEnriched())->toBeFalse()
        ->and(Video::factory()->make(['intro' => null, 'summary' => 'y'])->isEnriched())->toBeFalse()
        ->and(Video::factory()->make(['intro' => null, 'summary' => null])->isEnriched())->toBeFalse();
});

it('reports transcript presence', function () {
    expect(Video::factory()->make(['transcript' => '<p>texte</p>'])->hasTranscript())->toBeTrue()
        ->and(Video::factory()->make(['transcript' => null])->hasTranscript())->toBeFalse()
        ->and(Video::factory()->make(['transcript' => ''])->hasTranscript())->toBeFalse();
});
