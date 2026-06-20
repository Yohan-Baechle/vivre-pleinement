<?php

use App\Console\Commands\FixPostSeo;

it('demotes a content h1 to h2', function () {
    expect(FixPostSeo::fix('<h1><strong>Zenspire</strong></h1>'))
        ->toBe('<h2><strong>Zenspire</strong></h2>');
});

it('keeps existing h2 untouched', function () {
    expect(FixPostSeo::fix('<h2>Sous-titre</h2>'))->toBe('<h2>Sous-titre</h2>');
});

it('fills an empty alt on a linked yoga affiliate banner', function () {
    $in = '<a href="http://go.x.1tpe.net"><img loading="lazy" src="/storage/blog-images/yoga_300x250.gif" alt="" title="yoga_300x250"></a>';
    expect(FixPostSeo::fix($in))->toContain('alt="Séance de yoga pour apaiser l\'anxiété"');
});

it('marks affiliate links as sponsored with target blank', function () {
    $in = '<a href="http://go.laura542.neoaid.12.1tpe.net">lien</a>';
    $out = FixPostSeo::fix($in);

    expect($out)->toContain('rel="nofollow sponsored noopener"')
        ->and($out)->toContain('target="_blank"')
        ->and($out)->toContain('href="http://go.laura542.neoaid.12.1tpe.net"');
});

it('does not double-add rel on an already-marked affiliate link', function () {
    $in = '<a href="http://x.1tpe.net" rel="nofollow">lien</a>';
    $out = FixPostSeo::fix($in);

    expect(substr_count($out, 'rel='))->toBe(1);
});

it('marks hypnocaments and ruedesplantes affiliate links', function () {
    $in1 = '<a href="https://hypnocaments.com?partnerid=ABC">hypnose</a>';
    $in2 = '<a href="https://www.ruedesplantes.com/index.php?ref=290">plantes</a>';

    expect(FixPostSeo::fix($in1))->toContain('sponsored')
        ->and(FixPostSeo::fix($in2))->toContain('sponsored');
});

it('completes an existing rel="noopener" with nofollow sponsored', function () {
    $in = '<a href="https://hypnocaments.com?partnerid=x" rel="noopener">hypnose</a>';
    $out = FixPostSeo::fix($in);

    expect($out)->toContain('nofollow')
        ->and($out)->toContain('sponsored')
        ->and(substr_count($out, 'rel='))->toBe(1);
});

it('leaves a normal internal link untouched', function () {
    $in = '<a href="/blog/autre-article">voir aussi</a>';
    expect(FixPostSeo::fix($in))->toBe($in);
});

it('removes an orphan yoga banner (image without a link)', function () {
    $in = '<p>Texte</p><img src="/storage/blog-images/yoga_300x250.gif" alt="yoga"><p>Suite</p>';
    expect(FixPostSeo::fix($in))->toBe('<p>Texte</p><p>Suite</p>');
});

it('keeps a yoga banner wrapped in an affiliate link', function () {
    $in = '<a href="http://go.x.1tpe.net"><img src="/storage/blog-images/yoga_300x250.gif" alt="yoga"></a>';
    expect(FixPostSeo::fix($in))->toContain('yoga_300x250')
        ->and(FixPostSeo::fix($in))->toContain('1tpe.net');
});

it('replaces a broken Divi smiley image with a real emoji', function () {
    $in = '<p>Merci <img decoding="async" src="https://vivre-pleinement.fr/wp-content/themes/Divi/includes/builder/frontend-builder/assets/vendors/plugins/emoticons/img/smiley-smile.gif" alt="smile"></p>';
    $out = FixPostSeo::fix($in);

    expect($out)->toBe('<p>Merci 🙂</p>')
        ->and($out)->not->toContain('wp-content');
});
