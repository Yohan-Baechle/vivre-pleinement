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

it('removes the simple "Si vous aimez mon travail" paragraph', function () {
    $in = "<p>Conclusion.</p>\n<p>Si vous aimez mon travail 🙂</p>";
    expect(CleanPostContent::clean($in))->toBe('<p>Conclusion.</p>');
});

it('removes the parenthesised variants', function () {
    expect(CleanPostContent::clean("<p>Fin.</p>\n<p>(Si vous aimez mon travail 🙂)</p>"))
        ->toBe('<p>Fin.</p>');

    expect(CleanPostContent::clean("<p>Fin.</p>\n<p>(Si vous aimez mon travail) 🙂</p>"))
        ->toBe('<p>Fin.</p>');
});

it('removes the long Tipeee donation paragraph', function () {
    $in = "<p>Fin.</p>\n<p>Si vous aimez mon travail, vous pouvez me faire un don sur Tipeee. Même 1 euros fait toute la différence pour moi.🙂</p>";
    expect(CleanPostContent::clean($in))->toBe('<p>Fin.</p>');
});

it('leaves unrelated paragraphs untouched', function () {
    $in = '<p>J\'aime mon travail au quotidien.</p>';
    expect(CleanPostContent::clean($in))->toBe($in);
});

it('removes the migrated variants with broken emojis', function () {
    expect(CleanPostContent::clean("<p>Fin.</p>\n<p>(si mon travail vous aide ????)</p>"))
        ->toBe('<p>Fin.</p>');

    expect(CleanPostContent::clean("<p>Fin.</p>\n<p>(Si vous souhaitez soutenir mon travail ????)</p>"))
        ->toBe('<p>Fin.</p>');

    expect(CleanPostContent::clean("<p>Fin.</p>\n<p>Si vous appréciez mon travail ????</p>"))
        ->toBe('<p>Fin.</p>');
});

it('removes paragraphs mentioning Tipeee', function () {
    $in = "<p>Fin.</p>\n<p>Si mon travail vous aide à vous sentir mieux, vous pouvez me faire un petit donc sur Tipeee : </p>";
    expect(CleanPostContent::clean($in))->toBe('<p>Fin.</p>');
});

it('strips divi block comments and resulting empty paragraphs', function () {
    $in = '<p><!-- divi:paragraph -->Texte utile.</p><p><!-- /divi:buttons --></p><p> </p>';
    expect(CleanPostContent::clean($in))->toBe('<p>Texte utile.</p>');
});
