<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('preloads at most four font files (latin subset of above-the-fold variants only)', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect(substr_count($html, 'as="font"'))->toBeLessThanOrEqual(4);
});
