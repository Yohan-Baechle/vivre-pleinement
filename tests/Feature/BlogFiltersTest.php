<?php

use App\Support\BlogFilters;

it('keeps active filters and drops empty ones in the url', function () {
    $url = BlogFilters::url('blog.index', ['q' => 'stress', 'category' => '', 'sort' => null], ['tag' => 'act']);

    expect($url)->toContain('q=stress')
        ->and($url)->toContain('tag=act')
        ->and($url)->not->toContain('category=')
        ->and($url)->not->toContain('sort=');
});

it('removes a filter when merged with null', function () {
    $url = BlogFilters::url('blog.index', ['q' => 'stress', 'tag' => 'act'], ['tag' => null]);

    expect($url)->toContain('q=stress')
        ->and($url)->not->toContain('tag=');
});

it('builds chips only for resolvable active filters', function () {
    $categories = collect([(object) ['slug' => 'anxiete', 'name' => 'Anxiété']]);
    $tags = collect([(object) ['slug' => 'act', 'name' => 'ACT']]);

    $chips = BlogFilters::activeChips(
        ['q' => 'stress', 'category' => 'anxiete', 'tag' => 'inconnu'],
        $categories,
        $tags,
    );

    expect($chips)->toBe([
        ['label' => '« stress »', 'key' => 'q'],
        ['label' => 'Anxiété', 'key' => 'category'],
    ]);
});
