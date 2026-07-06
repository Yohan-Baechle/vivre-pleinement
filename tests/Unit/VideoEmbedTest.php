<?php

use App\Support\VideoEmbed;

it('extrait l\'id depuis une URL YouTube', function (string $url) {
    expect(VideoEmbed::parse($url))->toBe(['provider' => 'youtube', 'id' => 'inpok4MKVLM']);
})->with([
    'https://www.youtube.com/watch?v=inpok4MKVLM',
    'https://youtu.be/inpok4MKVLM',
    'https://www.youtube.com/embed/inpok4MKVLM',
]);

it('extrait l\'id depuis une URL Vimeo', function () {
    expect(VideoEmbed::parse('https://vimeo.com/123456789'))
        ->toBe(['provider' => 'vimeo', 'id' => '123456789']);
});

it('conserve un identifiant brut', function () {
    expect(VideoEmbed::parse('inpok4MKVLM')['id'])->toBe('inpok4MKVLM');
});

it('gère une valeur vide', function () {
    expect(VideoEmbed::parse(null))->toBe(['provider' => 'youtube', 'id' => null]);
});
