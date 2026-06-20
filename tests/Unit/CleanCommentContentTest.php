<?php

use App\Console\Commands\CleanCommentContent;

it('decodes HTML entities', function () {
    $in = '<p>Je pense avoir été dans le &#8220;trop sans attente&#8221; &#8230;</p>';

    expect(CleanCommentContent::clean($in))->toBe('Je pense avoir été dans le “trop sans attente” …');
});

it('decodes double-encoded ampersands', function () {
    expect(CleanCommentContent::clean('<p>vous &amp;amp; moi</p>'))->toBe('vous & moi');
});

it('strips wrapping paragraph tags', function () {
    expect(CleanCommentContent::clean('<p>Merci beaucoup 🙂</p>'))->toBe('Merci beaucoup 🙂');
});

it('turns paragraph breaks and <br> into newlines', function () {
    $in = '<p>Bonjour,</p><p>Oui et c’est bien cette peur.</p>';
    expect(CleanCommentContent::clean($in))->toBe("Bonjour,\n\nOui et c’est bien cette peur.");

    expect(CleanCommentContent::clean('<p>ligne 1<br>ligne 2</p>'))->toBe("ligne 1\nligne 2");
});

it('removes any other html tags', function () {
    expect(CleanCommentContent::clean('<p>un <strong>mot</strong> en gras</p>'))->toBe('un mot en gras');
});

it('leaves already-clean plain text untouched', function () {
    expect(CleanCommentContent::clean('Texte déjà propre.'))->toBe('Texte déjà propre.');
});
