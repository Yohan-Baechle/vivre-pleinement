<?php

use App\Support\Toc;

it('returns empty output for blank html', function () {
    expect(Toc::build(null))->toBe(['html' => '', 'items' => []]);
});

it('adds ids to headings and extracts the table of contents', function () {
    $result = Toc::build('<h2>Premier titre</h2><p>Texte</p><h3>Sous-titre</h3>');

    expect($result['items'])->toBe([
        ['level' => 2, 'id' => 'premier-titre', 'text' => 'Premier titre'],
        ['level' => 3, 'id' => 'sous-titre', 'text' => 'Sous-titre'],
    ])
        ->and($result['html'])->toContain('<h2 id="premier-titre">Premier titre</h2>')
        ->and($result['html'])->toContain('<h3 id="sous-titre">Sous-titre</h3>');
});

it('disambiguates duplicate headings', function () {
    $result = Toc::build('<h2>Titre</h2><h2>Titre</h2>');

    expect(collect($result['items'])->pluck('id')->all())->toBe(['titre', 'titre-2']);
});

it('keeps an existing id untouched', function () {
    $result = Toc::build('<h2 id="custom">Titre</h2>');

    expect($result['html'])->toContain('id="custom"')
        ->and($result['items'][0]['id'])->toBe('titre');
});

it('ignores empty headings', function () {
    $result = Toc::build('<h2></h2><h2>Vrai titre</h2>');

    expect($result['items'])->toHaveCount(1)
        ->and($result['items'][0]['text'])->toBe('Vrai titre');
});
