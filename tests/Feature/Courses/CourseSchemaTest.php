<?php

use App\Models\Course;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('emits a Course JSON-LD with a priced Offer on the course page', function () {
    $course = Course::factory()->create([
        'title' => 'Apaiser son anxiété au quotidien',
        'subtitle' => 'Un programme pas à pas.',
        'price_cents' => 14900,
        'currency' => 'EUR',
        'duration_minutes' => 420,
    ]);

    $html = $this->get(route('courses.show', $course))->assertOk()->getContent();

    expect($html)->toContain('"@type":"Course"')
        ->and($html)->toContain('"name":"Apaiser son anxiété au quotidien"')
        ->and($html)->toContain('"price":"149.00"')
        ->and($html)->toContain('"priceCurrency":"EUR"')
        ->and($html)->toContain('"courseMode":"Online"')
        ->and($html)->toContain('"courseWorkload":"PT420M"');
});

it('omits empty optional fields from the Course schema', function () {
    $course = Course::factory()->create(['duration_minutes' => null]);

    $html = $this->get(route('courses.show', $course))->assertOk()->getContent();

    expect($html)->toContain('"@type":"Course"')
        ->and($html)->not->toContain('courseWorkload');
});
