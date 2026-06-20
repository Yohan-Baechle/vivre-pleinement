<?php

use App\Support\AffiliateLinks;

it('turns an Amazon text link into a card with an Amazon button', function () {
    $in = '<p><a href="https://amzn.to/abc" target="_blank" rel="noopener">Procurez-vous le livre</a></p>';
    $out = AffiliateLinks::enhance($in);

    expect($out)->toContain('Procurez-vous le livre')
        ->and($out)->toContain('Voir sur Amazon')
        ->and($out)->toContain('href="https://amzn.to/abc"')
        ->and($out)->toContain('rel="nofollow sponsored noopener"');
});

it('turns an affiliate image banner into a card', function () {
    $in = '<a href="http://go.x.1tpe.net"><img src="/storage/blog-images/yoga_300x250.gif" alt="yoga"></a>';
    $out = AffiliateLinks::enhance($in);

    expect($out)->toContain('Découvrir')
        ->and($out)->not->toContain('<img');
});

it('does not create a card inside a card (single pass)', function () {
    $in = '<a href="https://amzn.to/abc">Le livre</a>';
    $out = AffiliateLinks::enhance($in);

    // Une seule carte générée, pas d'imbrication (un seul bouton CTA).
    expect(substr_count($out, 'Voir sur Amazon'))->toBe(1)
        ->and(substr_count($out, 'Lien partenaire'))->toBe(1);
});

it('types the card by product (hypnosis gets its own eyebrow and cta)', function () {
    $in = '<a href="https://hypnocaments.com?partnerid=x">hypnose</a>';
    $out = AffiliateLinks::enhance($in);

    expect($out)->toContain('Séance d’hypnose en ligne')
        ->and($out)->toContain('Écouter un extrait');
});

it('leaves non-affiliate links untouched', function () {
    $in = '<a href="/blog/autre">voir aussi</a>';
    expect(AffiliateLinks::enhance($in))->toBe($in);
});
