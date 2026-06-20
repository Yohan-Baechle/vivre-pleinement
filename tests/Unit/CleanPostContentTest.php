<?php

use App\Console\Commands\CleanPostContent;

it('unwraps a bare span keeping its text', function () {
    expect(CleanPostContent::clean('<h2><span>Comment stopper les ruminations ?</span></h2>'))
        ->toBe('<h2>Comment stopper les ruminations ?</h2>');
});

it('handles multiple spans in a paragraph', function () {
    $in = '<p><span>Phrase une.</span> <span>Phrase deux.</span></p>';
    expect(CleanPostContent::clean($in))->toBe('<p>Phrase une. Phrase deux.</p>');
});

it('unwraps nested bare spans', function () {
    expect(CleanPostContent::clean('<p><span><span>imbriqué</span></span></p>'))
        ->toBe('<p>imbriqué</p>');
});

it('leaves attributed spans untouched', function () {
    $in = '<p><span class="highlight">important</span></p>';
    expect(CleanPostContent::clean($in))->toBe('<p><span class="highlight">important</span></p>');
});

it('leaves clean content unchanged', function () {
    expect(CleanPostContent::clean('<p>Déjà propre.</p>'))->toBe('<p>Déjà propre.</p>');
});

it('preserves images and other tags', function () {
    $in = '<p><span>Voir </span><img src="/x.jpg" alt="y"></p>';
    expect(CleanPostContent::clean($in))->toBe('<p>Voir <img src="/x.jpg" alt="y"></p>');
});
